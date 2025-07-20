<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Aggregator;
use App\Models\Service;
use App\Models\ServiceMapping;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AlertService
{
    /**
     * Check for service health issues
     */
    public function checkServiceHealth()
    {
        $serviceMappings = ServiceMapping::with(['service', 'aggregator'])->active()->get();
        
        foreach ($serviceMappings as $mapping) {
            $this->checkServiceMappingHealth($mapping);
        }
    }
    
    /**
     * Check health for specific service mapping
     */
    protected function checkServiceMappingHealth(ServiceMapping $mapping)
    {
        $cacheKey = "health_check:{$mapping->id}";
        $lastCheck = Cache::get($cacheKey);
        
        // Check every 5 minutes
        if ($lastCheck && now()->diffInMinutes($lastCheck) < 5) {
            return;
        }
        
        try {
            $esbService = app(EsbService::class);
            $health = $esbService->getServiceHealth($mapping);
            
            if ($health['status'] === 'unhealthy') {
                $this->createAlert(
                    $mapping,
                    'service_down',
                    'high',
                    "Service {$mapping->service->name} is down",
                    "The service {$mapping->service->name} provided by {$mapping->aggregator->name} is currently unavailable.",
                    ['health_data' => $health]
                );
            } else {
                // Resolve any existing service down alerts
                $this->resolveAlerts($mapping, 'service_down');
            }
            
            Cache::put($cacheKey, now(), 300);
            
        } catch (\Exception $e) {
            Log::error('Health check failed', [
                'service_mapping_id' => $mapping->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check for high error rates
     */
    public function checkErrorRates()
    {
        $serviceMappings = ServiceMapping::with(['service', 'aggregator'])->active()->get();
        
        foreach ($serviceMappings as $mapping) {
            $this->checkServiceMappingErrorRate($mapping);
        }
    }
    
    /**
     * Check error rate for specific service mapping
     */
    protected function checkServiceMappingErrorRate(ServiceMapping $mapping)
    {
        // Check last hour
        $stats = app(EsbService::class)->getServiceStats($mapping, '1h');
        
        if ($stats['total_requests'] > 0) {
            $errorRate = (($stats['total_requests'] - $stats['successful_requests']) / $stats['total_requests']) * 100;
            
            if ($errorRate > 20) { // More than 20% error rate
                $this->createAlert(
                    $mapping,
                    'high_error_rate',
                    $errorRate > 50 ? 'critical' : 'high',
                    "High error rate for {$mapping->service->name}",
                    "The service {$mapping->service->name} has a {$errorRate}% error rate in the last hour.",
                    ['error_rate' => $errorRate, 'stats' => $stats]
                );
            } else {
                // Resolve any existing high error rate alerts
                $this->resolveAlerts($mapping, 'high_error_rate');
            }
        }
    }
    
    /**
     * Check for performance degradation
     */
    public function checkPerformance()
    {
        $serviceMappings = ServiceMapping::with(['service', 'aggregator'])->active()->get();
        
        foreach ($serviceMappings as $mapping) {
            $this->checkServiceMappingPerformance($mapping);
        }
    }
    
    /**
     * Check performance for specific service mapping
     */
    protected function checkServiceMappingPerformance(ServiceMapping $mapping)
    {
        $stats = app(EsbService::class)->getServiceStats($mapping, '1h');
        
        if ($stats['average_response_time'] > 5000) { // More than 5 seconds
            $this->createAlert(
                $mapping,
                'performance_degradation',
                'medium',
                "Performance degradation for {$mapping->service->name}",
                "The service {$mapping->service->name} has an average response time of {$stats['average_response_time']}ms.",
                ['response_time' => $stats['average_response_time'], 'stats' => $stats]
            );
        } else {
            // Resolve any existing performance alerts
            $this->resolveAlerts($mapping, 'performance_degradation');
        }
    }
    
    /**
     * Check for rate limit violations
     */
    public function checkRateLimits()
    {
        $clients = \App\Models\Client::active()->get();
        
        foreach ($clients as $client) {
            $this->checkClientRateLimits($client);
        }
    }
    
    /**
     * Check rate limits for specific client
     */
    protected function checkClientRateLimits($client)
    {
        $services = $client->services;
        
        foreach ($services as $service) {
            $cacheKey = "rate_limit:{$client->id}:{$service->code}";
            $currentCount = Cache::get($cacheKey, 0);
            $rateLimit = $service->pivot->rate_limit ?? 100;
            
            if ($currentCount >= $rateLimit * 0.9) { // 90% of rate limit
                $this->createAlert(
                    $client,
                    'rate_limit_exceeded',
                    'medium',
                    "Rate limit warning for {$client->name}",
                    "Client {$client->name} is approaching their rate limit for {$service->name}.",
                    ['current_count' => $currentCount, 'rate_limit' => $rateLimit, 'service' => $service->name]
                );
            }
        }
    }
    
    /**
     * Create an alert
     */
    public function createAlert($alertable, $type, $severity, $title, $message, $metadata = [])
    {
        // Check if similar alert already exists
        $existingAlert = Alert::where('alertable_type', get_class($alertable))
                             ->where('alertable_id', $alertable->id)
                             ->where('type', $type)
                             ->where('status', 'active')
                             ->first();
        
        if ($existingAlert) {
            // Update existing alert
            $existingAlert->update([
                'message' => $message,
                'metadata' => array_merge($existingAlert->metadata ?? [], $metadata),
                'updated_at' => now(),
            ]);
            
            return $existingAlert;
        }
        
        // Create new alert
        return Alert::create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'severity' => $severity,
            'status' => 'active',
            'alertable_type' => get_class($alertable),
            'alertable_id' => $alertable->id,
            'metadata' => $metadata,
        ]);
    }
    
    /**
     * Resolve alerts for a specific entity and type
     */
    public function resolveAlerts($alertable, $type)
    {
        return Alert::where('alertable_type', get_class($alertable))
                   ->where('alertable_id', $alertable->id)
                   ->where('type', $type)
                   ->where('status', 'active')
                   ->update([
                       'status' => 'resolved',
                       'resolved_at' => now(),
                       'resolved_by' => auth()->id(),
                   ]);
    }
    
    /**
     * Get active alerts summary
     */
    public function getActiveAlertsSummary()
    {
        $alerts = Alert::active()->get();
        
        return [
            'total' => $alerts->count(),
            'critical' => $alerts->where('severity', 'critical')->count(),
            'high' => $alerts->where('severity', 'high')->count(),
            'medium' => $alerts->where('severity', 'medium')->count(),
            'low' => $alerts->where('severity', 'low')->count(),
            'by_type' => $alerts->groupBy('type')->map->count(),
        ];
    }
    
    /**
     * Send alert notifications
     */
    public function sendAlertNotifications()
    {
        $criticalAlerts = Alert::active()->bySeverity('critical')->get();
        
        foreach ($criticalAlerts as $alert) {
            $this->sendNotification($alert);
        }
    }
    
    /**
     * Send notification for an alert
     */
    protected function sendNotification(Alert $alert)
    {
        // This could integrate with email, SMS, Slack, etc.
        Log::warning('Alert Notification', [
            'alert_id' => $alert->id,
            'title' => $alert->title,
            'message' => $alert->message,
            'severity' => $alert->severity,
            'type' => $alert->type,
        ]);
        
        // Example: Send to Slack
        // $this->sendToSlack($alert);
        
        // Example: Send email
        // $this->sendEmail($alert);
    }
} 