<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentLinkItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_link_id',
        'item_code',
        'item_name',
        'item_description',
        'amount',
        'paid_amount',
        'currency',
        'is_required',
        'allow_partial',
        'minimum_amount',
        'quantity',
        'unit',
        'metadata',
        'category',
        'subcategory',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'metadata' => 'array',
        'is_required' => 'boolean',
        'allow_partial' => 'boolean',
        'paid_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Generate item_code when creating
        static::creating(function ($item) {
            if (empty($item->item_code)) {
                $item->item_code = 'ITEM_' . Str::random(12);
            }
        });
    }

    /**
     * Get the payment link for this item
     */
    public function paymentLink(): BelongsTo
    {
        return $this->belongsTo(PaymentLink::class);
    }

    /**
     * Get the remaining amount to be paid
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->amount - $this->paid_amount;
    }

    /**
     * Get the payment percentage
     */
    public function getPaymentPercentageAttribute(): float
    {
        if ($this->amount == 0) {
            return 0;
        }
        return round(($this->paid_amount / $this->amount) * 100, 2);
    }

    /**
     * Check if item is fully paid
     */
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->paid_amount >= $this->amount;
    }

    /**
     * Check if item is partially paid
     */
    public function getIsPartiallyPaidAttribute(): bool
    {
        return $this->paid_amount > 0 && $this->paid_amount < $this->amount;
    }

    /**
     * Check if item can be paid partially
     */
    public function canPayPartially(float $amount): bool
    {
        if (!$this->allow_partial) {
            return $amount == $this->remaining_amount;
        }

        if ($this->minimum_amount && $amount < $this->minimum_amount) {
            return false;
        }

        return $amount <= $this->remaining_amount;
    }

    /**
     * Record a payment for this item
     */
    public function recordPayment(float $amount): void
    {
        $this->increment('paid_amount', $amount);
        
        // Update status based on payment
        if ($this->paid_amount >= $this->amount) {
            $this->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);
        } elseif ($this->paid_amount > 0) {
            $this->update(['status' => 'partial']);
        }
    }

    /**
     * Get display amount with quantity
     */
    public function getDisplayAmountAttribute(): string
    {
        if ($this->quantity > 1) {
            return number_format($this->amount, 2) . ' (' . $this->quantity . ' ' . ($this->unit ?? 'items') . ')';
        }
        return number_format($this->amount, 2);
    }

    /**
     * Get total amount including quantity
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->amount * $this->quantity;
    }

    /**
     * Scope for pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for paid items
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for partially paid items
     */
    public function scopePartiallyPaid($query)
    {
        return $query->where('status', 'partial');
    }

    /**
     * Scope for required items
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope for items by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
} 