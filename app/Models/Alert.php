<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'severity',
        'status',
        'alertable_type',
        'alertable_id',
        'metadata',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the parent alertable model
     */
    public function alertable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who acknowledged the alert
     */
    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Get the user who resolved the alert
     */
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope for active alerts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for alerts by severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for alerts by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get severity badge class
     */
    public function getSeverityBadgeClassAttribute()
    {
        return match($this->severity) {
            'critical' => 'bg-red-100 text-red-800',
            'high' => 'bg-orange-100 text-orange-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'low' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeClassAttribute()
    {
        return match($this->type) {
            'service_down' => 'bg-red-100 text-red-800',
            'high_error_rate' => 'bg-orange-100 text-orange-800',
            'rate_limit_exceeded' => 'bg-yellow-100 text-yellow-800',
            'performance_degradation' => 'bg-blue-100 text-blue-800',
            'security_breach' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Check if alert is acknowledged
     */
    public function isAcknowledged()
    {
        return !is_null($this->acknowledged_at);
    }

    /**
     * Check if alert is resolved
     */
    public function isResolved()
    {
        return !is_null($this->resolved_at);
    }

    /**
     * Acknowledge the alert
     */
    public function acknowledge($userId = null)
    {
        $this->update([
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Resolve the alert
     */
    public function resolve($userId = null)
    {
        $this->update([
            'resolved_at' => now(),
            'resolved_by' => $userId ?? auth()->id(),
            'status' => 'resolved',
        ]);
    }
} 