<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Aggregator extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'api_endpoint',
        'api_key',
        'api_secret',
        'webhook_url',
        'status',
        'rate_limit',
        'timeout',
        'retry_attempts',
        'contact_person',
        'contact_email',
        'contact_phone',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'status' => 'boolean',
        'rate_limit' => 'integer',
        'timeout' => 'integer',
        'retry_attempts' => 'integer',
    ];

    /**
     * Get the clients that can access this aggregator
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_aggregator')
                    ->withPivot(['status', 'api_credentials', 'rate_limit', 'created_at'])
                    ->withTimestamps();
    }

    /**
     * Get the services provided by this aggregator
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the service mappings for this aggregator
     */
    public function serviceMappings(): HasMany
    {
        return $this->hasMany(ServiceMapping::class);
    }

    /**
     * Get the transactions for this aggregator
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope for active aggregators
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get aggregator's service count
     */
    public function getServiceCountAttribute()
    {
        return $this->services()->count();
    }

    /**
     * Get aggregator's client count
     */
    public function getClientCountAttribute()
    {
        return $this->clients()->count();
    }

    /**
     * Get aggregator's transaction count for a specific period
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
     * Get alerts for this aggregator
     */
    public function alerts(): MorphMany
    {
        return $this->morphMany(Alert::class, 'alertable');
    }
} 