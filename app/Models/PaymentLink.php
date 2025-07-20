<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'link_id',
        'short_code',
        'client_reference',
        'client_id',
        'service_mapping_id',
        'amount',
        'currency',
        'description',
        'narration',
        'customer_phone',
        'customer_name',
        'customer_email',
        'payment_method',
        'allowed_networks',
        'allow_partial_payment',
        'minimum_amount',
        'maximum_amount',
        'expires_at',
        'max_uses',
        'current_uses',
        'status',
        'is_reusable',
        'is_public',
        'metadata',
        'settings',
        'webhook_url',
        'success_url',
        'failure_url',
        'cancel_url',
        'views_count',
        'last_viewed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_amount' => 'decimal:2',
        'allowed_networks' => 'array',
        'metadata' => 'array',
        'settings' => 'array',
        'allow_partial_payment' => 'boolean',
        'is_reusable' => 'boolean',
        'is_public' => 'boolean',
        'expires_at' => 'datetime',
        'last_viewed_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Generate link_id and short_code when creating
        static::creating(function ($paymentLink) {
            if (empty($paymentLink->link_id)) {
                $paymentLink->link_id = 'LINK_' . Str::random(16);
            }
            if (empty($paymentLink->short_code)) {
                $paymentLink->short_code = self::generateShortCode();
            }
        });
    }

    /**
     * Get the client for this payment link
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the service mapping for this payment link
     */
    public function serviceMapping(): BelongsTo
    {
        return $this->belongsTo(ServiceMapping::class);
    }

    /**
     * Get the transactions for this payment link
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'client_reference', 'client_reference');
    }

    /**
     * Get the items for this payment link
     */
    public function items(): HasMany
    {
        return $this->hasMany(PaymentLinkItem::class);
    }

    /**
     * Generate a unique short code
     */
    public static function generateShortCode(): string
    {
        do {
            $shortCode = Str::random(8);
        } while (self::where('short_code', $shortCode)->exists());

        return $shortCode;
    }

    /**
     * Get the full payment URL
     */
    public function getPaymentUrlAttribute(): string
    {
        return config('app.url') . '/pay/' . $this->short_code;
    }

    /**
     * Get the QR code data
     */
    public function getQrCodeDataAttribute(): string
    {
        return $this->payment_url;
    }

    /**
     * Check if the link is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * Check if the link can be used
     */
    public function getCanBeUsedAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->is_expired) {
            return false;
        }

        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
        $this->update(['last_viewed_at' => now()]);
    }

    /**
     * Increment usage count
     */
    public function incrementUses(): void
    {
        $this->increment('current_uses');
        
        // If not reusable and max uses reached, mark as completed
        if (!$this->is_reusable && $this->max_uses && $this->current_uses >= $this->max_uses) {
            $this->update(['status' => 'completed']);
        }
    }

    /**
     * Mark link as expired
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Mark link as cancelled
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Get total amount collected
     */
    public function getTotalCollectedAttribute(): float
    {
        return $this->transactions()
                    ->where('status', 'success')
                    ->sum('amount');
    }

    /**
     * Get total amount from items
     */
    public function getTotalItemsAmountAttribute(): float
    {
        return $this->items->sum('amount');
    }

    /**
     * Get total paid amount from items
     */
    public function getTotalItemsPaidAttribute(): float
    {
        return $this->items->sum('paid_amount');
    }

    /**
     * Get remaining amount from items
     */
    public function getRemainingItemsAmountAttribute(): float
    {
        return $this->total_items_amount - $this->total_items_paid;
    }

    /**
     * Get payment progress percentage
     */
    public function getPaymentProgressAttribute(): float
    {
        if ($this->total_items_amount == 0) {
            return 0;
        }
        return round(($this->total_items_paid / $this->total_items_amount) * 100, 2);
    }

    /**
     * Get successful transactions count
     */
    public function getSuccessfulTransactionsCountAttribute(): int
    {
        return $this->transactions()
                    ->where('status', 'success')
                    ->count();
    }

    /**
     * Get conversion rate (views to payments)
     */
    public function getConversionRateAttribute(): float
    {
        if ($this->views_count === 0) {
            return 0;
        }
        return round(($this->successful_transactions_count / $this->views_count) * 100, 2);
    }

    /**
     * Scope for active links
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for expired links
     */
    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')
              ->orWhere('expires_at', '<', now());
        });
    }

    /**
     * Scope for links by client
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope for public links
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get allowed networks as array
     */
    public function getAllowedNetworksArrayAttribute(): array
    {
        return $this->allowed_networks ?? [
            'TZ-AIRTEL-C2B',
            'TZ-TIGO-C2B', 
            'TZ-MPESA-C2B',
            'TZ-HALOPESA-C2B'
        ];
    }

    /**
     * Check if a specific network is allowed
     */
    public function isNetworkAllowed(string $network): bool
    {
        return in_array($network, $this->allowed_networks_array);
    }

    /**
     * Get payment amount for a specific network (considering partial payments)
     */
    public function getPaymentAmount(string $network = null): float
    {
        if ($this->allow_partial_payment) {
            return $this->minimum_amount ?? $this->amount;
        }
        return $this->amount;
    }

    /**
     * Validate payment amount
     */
    public function validatePaymentAmount(float $amount): bool
    {
        if ($this->minimum_amount && $amount < $this->minimum_amount) {
            return false;
        }

        if ($this->maximum_amount && $amount > $this->maximum_amount) {
            return false;
        }

        if (!$this->allow_partial_payment && $amount != $this->amount) {
            return false;
        }

        return true;
    }
} 