<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'external_transaction_id',
        'aggregator_reference',
        'client_reference',
        'client_id',
        'aggregator_id',
        'service_id',
        'service_mapping_id',
        'amount',
        'currency',
        'fee_amount',
        'commission_amount',
        'customer_phone',
        'customer_name',
        'mobile_network',
        'network_provider',
        'description',
        'narration',
        'channel',
        'payment_method',
        'request_data',
        'original_request',
        'transformed_request',
        'aggregator_request',
        'aggregator_response',
        'transformed_response',
        'final_response',
        'response_data',
        'status',
        'aggregator_status',
        'client_status',
        'error_message',
        'response_time',
        'esb_processing_time',
        'aggregator_response_time',
        'total_processing_time',
        'request_size',
        'response_size',
        'retry_count',
        'last_retry_at',
        'request_signature',
        'response_signature',
        'checksum',
        'is_encrypted',
        'webhook_url',
        'webhook_attempts',
        'last_webhook_sent_at',
        'webhook_responses',
        'is_reconciled',
        'reconciled_at',
        'reconciled_by',
        'reconciliation_notes',
        'created_by',
        'updated_by',
        'audit_trail',
        'session_id',
        'correlation_id',
        'trace_id',
        'tags',
        'risk_level',
        'is_suspicious',
        'risk_notes',
        'requires_manual_review',
        'is_settled',
        'settled_at',
        'settlement_reference',
        'settlement_amount',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'request_data' => 'array',
        'original_request' => 'array',
        'transformed_request' => 'array',
        'aggregator_request' => 'array',
        'aggregator_response' => 'array',
        'transformed_response' => 'array',
        'final_response' => 'array',
        'response_data' => 'array',
        'webhook_responses' => 'array',
        'audit_trail' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'response_time' => 'float',
        'esb_processing_time' => 'float',
        'aggregator_response_time' => 'float',
        'total_processing_time' => 'float',
        'request_size' => 'integer',
        'response_size' => 'integer',
        'retry_count' => 'integer',
        'webhook_attempts' => 'integer',
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'settlement_amount' => 'decimal:2',
        'is_encrypted' => 'boolean',
        'is_reconciled' => 'boolean',
        'is_suspicious' => 'boolean',
        'requires_manual_review' => 'boolean',
        'is_settled' => 'boolean',
        'aggregator_processed_at' => 'datetime',
        'client_notified_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'last_webhook_sent_at' => 'datetime',
        'reconciled_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    /**
     * Get the client for this transaction
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the aggregator for this transaction
     */
    public function aggregator(): BelongsTo
    {
        return $this->belongsTo(Aggregator::class);
    }

    /**
     * Get the service for this transaction
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the service mapping for this transaction
     */
    public function serviceMapping(): BelongsTo
    {
        return $this->belongsTo(ServiceMapping::class);
    }

    /**
     * Scope for successful transactions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for transactions by client
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope for transactions by aggregator
     */
    public function scopeByAggregator($query, $aggregatorId)
    {
        return $query->where('aggregator_id', $aggregatorId);
    }

    /**
     * Scope for transactions by service
     */
    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Scope for transactions by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for transactions today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for transactions this week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope for transactions this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month);
    }

    /**
     * Get transaction status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'success' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'timeout' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get transaction duration in milliseconds
     */
    public function getDurationAttribute()
    {
        return $this->response_time ? round($this->response_time * 1000, 2) : null;
    }

    /**
     * Get transaction size in KB
     */
    public function getTotalSizeAttribute()
    {
        $total = ($this->request_size ?? 0) + ($this->response_size ?? 0);
        return round($total / 1024, 2);
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccessful()
    {
        return $this->status === 'success';
    }

    /**
     * Check if transaction failed
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Get error summary
     */
    public function getErrorSummaryAttribute()
    {
        if ($this->isSuccessful()) {
            return null;
        }

        return $this->error_message ?: 'Unknown error occurred';
    }

    /**
     * Scope for reconciled transactions
     */
    public function scopeReconciled($query)
    {
        return $query->where('is_reconciled', true);
    }

    /**
     * Scope for unreconciled transactions
     */
    public function scopeUnreconciled($query)
    {
        return $query->where('is_reconciled', false);
    }

    /**
     * Scope for settled transactions
     */
    public function scopeSettled($query)
    {
        return $query->where('is_settled', true);
    }

    /**
     * Scope for unsettled transactions
     */
    public function scopeUnsettled($query)
    {
        return $query->where('is_settled', false);
    }

    /**
     * Scope for high-risk transactions
     */
    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', 'high');
    }

    /**
     * Scope for suspicious transactions
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope for transactions requiring manual review
     */
    public function scopeRequiresReview($query)
    {
        return $query->where('requires_manual_review', true);
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute()
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get total amount including fees
     */
    public function getTotalAmountAttribute()
    {
        return $this->amount + ($this->fee_amount ?? 0) + ($this->commission_amount ?? 0);
    }

    /**
     * Check if transaction is reconciled
     */
    public function isReconciled()
    {
        return $this->is_reconciled;
    }

    /**
     * Check if transaction is settled
     */
    public function isSettled()
    {
        return $this->is_settled;
    }

    /**
     * Check if transaction requires manual review
     */
    public function requiresManualReview()
    {
        return $this->requires_manual_review;
    }

    /**
     * Check if transaction is suspicious
     */
    public function isSuspicious()
    {
        return $this->is_suspicious;
    }

    /**
     * Get risk level badge class
     */
    public function getRiskLevelBadgeClassAttribute()
    {
        return match($this->risk_level) {
            'low' => 'bg-green-100 text-green-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'high' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Add audit trail entry
     */
    public function addAuditTrail($action, $details = [], $user = null)
    {
        $auditTrail = $this->audit_trail ?? [];
        $auditTrail[] = [
            'action' => $action,
            'details' => $details,
            'user' => $user,
            'timestamp' => now()->toISOString(),
            'ip_address' => request()->ip(),
        ];

        $this->update(['audit_trail' => $auditTrail]);
    }

    /**
     * Update webhook response
     */
    public function updateWebhookResponse($response, $statusCode)
    {
        $webhookResponses = $this->webhook_responses ?? [];
        $webhookResponses[] = [
            'response' => $response,
            'status_code' => $statusCode,
            'timestamp' => now()->toISOString(),
        ];

        $this->update([
            'webhook_responses' => $webhookResponses,
            'webhook_attempts' => $this->webhook_attempts + 1,
            'last_webhook_sent_at' => now(),
        ]);
    }

    /**
     * Mark as reconciled
     */
    public function markAsReconciled($by = null, $notes = null)
    {
        $this->update([
            'is_reconciled' => true,
            'reconciled_at' => now(),
            'reconciled_by' => $by,
            'reconciliation_notes' => $notes,
        ]);

        $this->addAuditTrail('reconciled', [
            'reconciled_by' => $by,
            'notes' => $notes
        ], $by);
    }

    /**
     * Mark as settled
     */
    public function markAsSettled($reference = null, $amount = null)
    {
        $this->update([
            'is_settled' => true,
            'settled_at' => now(),
            'settlement_reference' => $reference,
            'settlement_amount' => $amount,
        ]);

        $this->addAuditTrail('settled', [
            'settlement_reference' => $reference,
            'settlement_amount' => $amount
        ]);
    }

    /**
     * Get transaction summary for reporting
     */
    public function getTransactionSummaryAttribute()
    {
        return [
            'id' => $this->transaction_id,
            'external_id' => $this->external_transaction_id,
            'client' => $this->client->name ?? 'Unknown',
            'aggregator' => $this->aggregator->name ?? 'Unknown',
            'service' => $this->service->name ?? 'Unknown',
            'amount' => $this->formatted_amount,
            'status' => $this->status,
            'aggregator_status' => $this->aggregator_status,
            'customer_phone' => $this->customer_phone,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'processing_time' => $this->total_processing_time ? round($this->total_processing_time * 1000, 2) . 'ms' : 'N/A',
        ];
    }
} 