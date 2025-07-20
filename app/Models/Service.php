<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'aggregator_id',
        'endpoint',
        'method',
        'request_format',
        'response_format',
        'rate_limit',
        'timeout',
        'retry_attempts',
        'status',
        'settings',
        'documentation',
    ];

    protected $casts = [
        'settings' => 'array',
        'status' => 'boolean',
        'rate_limit' => 'integer',
        'timeout' => 'integer',
        'retry_attempts' => 'integer',
    ];

    /**
     * Get the aggregator that provides this service
     */
    public function aggregator(): BelongsTo
    {
        return $this->belongsTo(Aggregator::class);
    }

    /**
     * Get the clients that can consume this service
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_service')
                    ->withPivot(['status', 'rate_limit', 'quota', 'created_at'])
                    ->withTimestamps();
    }

    /**
     * Get the service mappings for this service
     */
    public function serviceMappings(): HasMany
    {
        return $this->hasMany(ServiceMapping::class);
    }

    /**
     * Get the transactions for this service
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope for active services
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope for services by aggregator
     */
    public function scopeByAggregator($query, $aggregatorId)
    {
        return $query->where('aggregator_id', $aggregatorId);
    }

    /**
     * Get service's transaction count for a specific period
     */
    public function getTransactionCount($period = 'today')
    {
        $query = $this->transactions();
        
        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month);
                break;
        }
        
        return $query->count();
    }

    /**
     * Get service's success rate
     */
    public function getSuccessRateAttribute()
    {
        $total = $this->transactions()->count();
        if ($total === 0) return 0;
        
        $successful = $this->transactions()->where('status', 'success')->count();
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get service's average response time
     */
    public function getAverageResponseTimeAttribute()
    {
        return $this->transactions()
                    ->whereNotNull('response_time')
                    ->avg('response_time');
    }
} 