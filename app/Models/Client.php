<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'api_key',
        'api_secret',
        'webhook_url',
        'status',
        'contact_person',
        'contact_email',
        'contact_phone',
        'address',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'status' => 'boolean',
    ];

    /**
     * Get the aggregators that this client can access
     */
    public function aggregators(): BelongsToMany
    {
        return $this->belongsToMany(Aggregator::class, 'client_aggregator')
                    ->withPivot(['status', 'api_credentials', 'rate_limit', 'created_at'])
                    ->withTimestamps();
    }

    /**
     * Get the services that this client can consume
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'client_service')
                    ->withPivot(['status', 'rate_limit', 'quota', 'created_at'])
                    ->withTimestamps();
    }

    /**
     * Get the service mappings for this client
     */
    public function serviceMappings(): HasMany
    {
        return $this->hasMany(ServiceMapping::class);
    }

    /**
     * Get the transactions for this client
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope for active clients
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get client's available services through aggregators
     */
    public function getAvailableServices()
    {
        return $this->aggregators()
                    ->with('services')
                    ->get()
                    ->pluck('services')
                    ->flatten()
                    ->unique('id');
    }

    /**
     * Get alerts for this client
     */
    public function alerts(): MorphMany
    {
        return $this->morphMany(Alert::class, 'alertable');
    }
} 