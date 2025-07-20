<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MonitorEsbHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esb:monitor-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor ESB health and create alerts for issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting ESB health monitoring...');
        
        $alertService = app(\App\Services\AlertService::class);
        
        // Check service health
        $this->info('Checking service health...');
        $alertService->checkServiceHealth();
        
        // Check error rates
        $this->info('Checking error rates...');
        $alertService->checkErrorRates();
        
        // Check performance
        $this->info('Checking performance...');
        $alertService->checkPerformance();
        
        // Check rate limits
        $this->info('Checking rate limits...');
        $alertService->checkRateLimits();
        
        // Send notifications for critical alerts
        $this->info('Sending alert notifications...');
        $alertService->sendAlertNotifications();
        
        $this->info('ESB health monitoring completed.');
    }
}
