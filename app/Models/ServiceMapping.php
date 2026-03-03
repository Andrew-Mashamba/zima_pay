<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ServiceMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'client_id',
        'aggregator_id',
        'service_id',
        'request_mapping',
        'response_mapping',
        'transformation_rules',
        'status',
        'priority',
        'settings',
    ];

    protected $casts = [
        'request_mapping' => 'array',
        'response_mapping' => 'array',
        'transformation_rules' => 'array',
        'settings' => 'array',
        'status' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Get the client for this mapping
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the aggregator for this mapping
     */
    public function aggregator(): BelongsTo
    {
        return $this->belongsTo(Aggregator::class);
    }

    /**
     * Get the service for this mapping
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the transactions for this mapping
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope for active mappings
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope for mappings by client
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope for mappings by aggregator
     */
    public function scopeByAggregator($query, $aggregatorId)
    {
        return $query->where('aggregator_id', $aggregatorId);
    }

    /**
     * Scope for mappings by service
     */
    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Get mapping's transaction count for a specific period
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
     * Get mapping's success rate
     */
    public function getSuccessRateAttribute()
    {
        $total = $this->transactions()->count();
        if ($total === 0) return 0;
        
        $successful = $this->transactions()->where('status', 'success')->count();
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Transform client request to aggregator format
     */
    public function transformRequest($clientRequest)
    {
        $transformed = [];
        
        // If request mapping is configured, use it
        if (!empty($this->request_mapping)) {
            foreach ($this->request_mapping as $clientField => $aggregatorField) {
                if (isset($clientRequest[$clientField])) {
                    $transformed[$aggregatorField] = $clientRequest[$clientField];
                }
            }
        } else {
            $aggregatorCode = strtoupper($this->aggregator->code ?? '');
            $isSelcom = $aggregatorCode === 'SELCOM';

            if (isset($clientRequest['startDate']) || isset($clientRequest['endDate'])) {
                $transformed = [
                    'startDate' => $clientRequest['startDate'] ?? null,
                    'endDate' => $clientRequest['endDate'] ?? null
                ];
            } elseif (isset($clientRequest['reference']) && !isset($clientRequest['amount'])) {
                $transformed = $isSelcom
                    ? ['transid' => $clientRequest['transaction_id'] ?? null, 'reference' => $clientRequest['reference'] ?? null]
                    : ['transactionId' => $clientRequest['reference'] ?? null];
            } else {
                if ($isSelcom) {
                    $transformed = [
                        'msisdn' => $clientRequest['customer_phone'] ?? null,
                        'utilityref' => $clientRequest['reference'] ?? null,
                        'amount' => $clientRequest['amount'] ?? null,
                    ];
                } else {
                    $transformed = [
                        'channel' => $clientRequest['mobile_network'] ?? null,
                        'msisdn' => $clientRequest['customer_phone'] ?? null,
                        'amount' => $clientRequest['amount'] ?? null,
                        'reference' => $clientRequest['reference'] ?? null,
                        'narration' => $clientRequest['description'] ?? null,
                        'transactionDate' => $clientRequest['date'] ?? null,
                        'callbackUrl' => $clientRequest['webhook_url'] ?? null
                    ];
                }
            }
        }
        
        // Apply transformation rules
        if ($this->transformation_rules) {
            foreach ($this->transformation_rules as $rule) {
                if (isset($rule['type']) && isset($rule['field'])) {
                    switch ($rule['type']) {
                        case 'format_date':
                            if (isset($transformed[$rule['field']])) {
                                $transformed[$rule['field']] = date($rule['format'], strtotime($transformed[$rule['field']]));
                            }
                            break;
                        case 'uppercase':
                            if (isset($transformed[$rule['field']])) {
                                $transformed[$rule['field']] = strtoupper($transformed[$rule['field']]);
                            }
                            break;
                        case 'lowercase':
                            if (isset($transformed[$rule['field']])) {
                                $transformed[$rule['field']] = strtolower($transformed[$rule['field']]);
                            }
                            break;
                    }
                }
            }
        }
        
        return $transformed;
    }

    /**
     * Transform aggregator response to client format
     */
    public function transformResponse($aggregatorResponse)
    {
        $transformed = [];
        
        // Map basic fields from response mapping
        foreach ($this->response_mapping as $aggregatorField => $clientField) {
            if (isset($aggregatorResponse[$aggregatorField])) {
                $transformed[$clientField] = $aggregatorResponse[$aggregatorField];
            }
        }
        
        // Selcom uses result/resultcode; Tembo uses statusCode
        $status = $aggregatorResponse['result'] ?? $aggregatorResponse['statusCode'] ?? $aggregatorResponse['status'] ?? 'success';
        $resultCode = $aggregatorResponse['resultcode'] ?? $aggregatorResponse['statusCode'] ?? null;
        if ($resultCode === '000') {
            $status = 'success';
        } elseif (in_array($resultCode, ['111', '927'])) {
            $status = 'pending';
        } elseif ($resultCode && $resultCode !== '000') {
            $status = 'failed';
        }

        // Enhanced response with comprehensive transaction details
        $transformed = array_merge($transformed, [
            'status' => $status,
            'message' => $aggregatorResponse['message'] ?? 'Transaction processed successfully',
            'transaction_id' => $aggregatorResponse['transactionId'] ?? $aggregatorResponse['transaction_id'] ?? null,
            'reference' => $aggregatorResponse['reference'] ?? $aggregatorResponse['transactionRef'] ?? null,
            'amount' => $aggregatorResponse['amount'] ?? null,
            'currency' => $aggregatorResponse['currency'] ?? 'TZS',
            'customer_phone' => $aggregatorResponse['customerPhone'] ?? $aggregatorResponse['msisdn'] ?? null,
            'mobile_network' => $aggregatorResponse['mobileNetwork'] ?? $aggregatorResponse['channel'] ?? null,
            'description' => $aggregatorResponse['description'] ?? $aggregatorResponse['narration'] ?? null,
            'aggregator_status' => $aggregatorResponse['aggregatorStatus'] ?? 'success',
            'processing_time' => $aggregatorResponse['processingTime'] ?? null,
            'timestamp' => $aggregatorResponse['timestamp'] ?? now()->toISOString(),
            'webhook_sent' => $aggregatorResponse['webhookSent'] ?? false,
            'aggregator_reference' => $aggregatorResponse['aggregatorReference'] ?? null,
            'network_provider' => $aggregatorResponse['networkProvider'] ?? null,
            'risk_level' => $aggregatorResponse['riskLevel'] ?? 'low',
            'commission' => $aggregatorResponse['commission'] ?? null,
            'transaction_fee' => $aggregatorResponse['transactionFee'] ?? null
        ]);
        
        return $transformed;
    }

    /**
     * Get alerts for this service mapping
     */
    public function alerts(): MorphMany
    {
        return $this->morphMany(Alert::class, 'alertable');
    }
} 