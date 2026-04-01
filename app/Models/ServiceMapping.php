<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Log;

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

        // TEMBO /collection requires: channel, narration, reference (transactionRef), transactionDate, callbackUrl; amount >= 1000
        // Tembo channel must be one of: TZ-TIGO-C2B, TZ-VODACOM-C2B, TZ-AIRTEL-C2B, TZ-HALOTEL-C2B
        $isTembo = strtoupper($this->aggregator->code ?? '') === 'TEMBO';
        if ($isTembo && isset($transformed['msisdn']) && isset($transformed['amount'])) {
            $channel = $transformed['channel'] ?? $clientRequest['mobile_network'] ?? 'TZ-VODACOM-C2B';
            $transformed['channel'] = $channel === 'TZ-MPESA-C2B' ? 'TZ-VODACOM-C2B' : $channel;
            $transformed['narration'] = $transformed['narration'] ?? $clientRequest['description'] ?? 'Payment';
            $transformed['transactionRef'] = $transformed['transactionRef'] ?? $transformed['utilityref'] ?? $clientRequest['reference'] ?? null;
            $transformed['reference'] = $transformed['reference'] ?? $transformed['transactionRef'];
            $transformed['transactionDate'] = $transformed['transactionDate'] ?? $clientRequest['date'] ?? now()->format('Y-m-d H:i:s');
            $transformed['callbackUrl'] = $transformed['callbackUrl'] ?? $clientRequest['webhook_url'] ?? null;
            if (isset($transformed['utilityref']) && !isset($transformed['transactionRef'])) {
                $transformed['transactionRef'] = $transformed['utilityref'];
            }
            unset($transformed['utilityref']);
        }
        
        return $transformed;
    }

    /**
     * Compact diagnostic line for Laravel logs (not shown to payers).
     */
    private function formatTemboFailureDetailForLog(array $r): string
    {
        if (isset($r['message']) && is_string($r['message']) && trim($r['message']) !== '') {
            return trim($r['message']);
        }

        $reason = $r['reason'] ?? '';
        $details = $r['details'] ?? null;
        if (is_string($reason) && $reason !== '') {
            $suffix = '';
            if ($details !== null && $details !== [] && $details !== '') {
                $suffix = ' — ' . (is_array($details) ? json_encode($details) : (string) $details);
            }

            return $reason . $suffix;
        }

        if ($details !== null && $details !== [] && $details !== '') {
            return is_array($details) ? json_encode($details) : (string) $details;
        }

        $sc = $r['statusCode'] ?? $r['status'] ?? 'UNKNOWN';
        $line = 'Tembo: ' . (is_scalar($sc) ? (string) $sc : json_encode($sc));
        if (!empty($r['transactionId'])) {
            $line .= ' | Transaction ID: ' . $r['transactionId'];
        }
        if (!empty($r['transactionRef'])) {
            $line .= ' | Reference: ' . $r['transactionRef'];
        }

        return $line;
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
        
        // Selcom uses result/resultcode; Tembo uses statusCode (string e.g. PENDING_ACK, PAYMENT_ACCEPTED)
        $status = $aggregatorResponse['result'] ?? $aggregatorResponse['statusCode'] ?? $aggregatorResponse['status'] ?? 'success';
        $resultCode = $aggregatorResponse['resultcode'] ?? $aggregatorResponse['statusCode'] ?? null;
        $isTembo = strtoupper($this->aggregator->code ?? '') === 'TEMBO';

        if ($isTembo && is_string($resultCode)) {
            // Tembo: statusCode values like PENDING_ACK, PAYMENT_ACCEPTED, SUCCESS
            $temboStatus = strtoupper((string) $resultCode);
            if (in_array($temboStatus, ['PAYMENT_ACCEPTED', 'SUCCESS', 'COMPLETED'])) {
                $status = 'success';
            } elseif (in_array($temboStatus, ['PENDING_ACK', 'PENDING', 'ACCEPTED'])) {
                $status = 'pending';
            } elseif (in_array($temboStatus, ['FAILED', 'REJECTED', 'CANCELLED', 'ERROR', 'PROVIDER_FAILED'])) {
                $status = 'failed';
            }
        } else {
            // Selcom-style numeric resultcode
            if ($resultCode === '000') {
                $status = 'success';
            } elseif (in_array($resultCode, ['111', '927'])) {
                $status = 'pending';
            } elseif ($resultCode && $resultCode !== '000') {
                $status = 'failed';
            }
        }

        $statusCode = $aggregatorResponse['statusCode'] ?? null;
        $message = $aggregatorResponse['message'] ?? null;

        if ($message === null && $status === 'failed' && $isTembo) {
            $httpCode = is_numeric($statusCode) ? (int) $statusCode : (is_numeric($resultCode) ? (int) $resultCode : 0);
            $reason = $aggregatorResponse['reason'] ?? '';
            if ($httpCode === 409 || $reason === 'DUPLICATE_REQUEST') {
                $message = 'Duplicate payment request. Please wait a moment or try again.';
            } elseif ($httpCode === 400 && $reason === 'VALIDATION_ERROR') {
                $message = 'Payment validation failed: ' . json_encode($aggregatorResponse['details'] ?? []);
            } elseif ($httpCode >= 400 && is_numeric($statusCode)) {
                $message = 'Payment could not be processed. Please try again or contact support.';
            }
        }

        if ($message === null && $status === 'failed' && $isTembo && is_string($statusCode)) {
            $message = match (strtoupper($statusCode)) {
                'PROVIDER_FAILED' => 'Payment was not completed by the mobile network or customer (e.g. cancelled, timeout, or insufficient balance).',
                'REJECTED', 'CANCELLED' => 'Payment was cancelled or rejected.',
                'FAILED', 'ERROR' => 'Payment could not be completed.',
                default => 'Payment could not be completed.',
            };
        }

        if ($message === null && $status === 'failed' && !$isTembo && is_string($statusCode)) {
            $message = match (strtoupper($statusCode)) {
                'PROVIDER_FAILED' => 'Payment was not completed by the mobile network or customer (e.g. cancelled, timeout, or insufficient balance).',
                'REJECTED', 'CANCELLED' => 'Payment was cancelled or rejected.',
                'FAILED', 'ERROR' => 'Payment could not be completed.',
                default => null,
            };
        }

        $message = $message ?? ($status === 'failed' ? 'Payment could not be completed.' : 'Transaction processed successfully');

        if ($status === 'failed' && $isTembo) {
            Log::warning('Tembo payment failure', [
                'summary' => $this->formatTemboFailureDetailForLog($aggregatorResponse),
                'aggregator_response' => $aggregatorResponse,
            ]);
        }

        // Enhanced response with comprehensive transaction details
        $transformed = array_merge($transformed, [
            'status' => $status,
            'message' => $message,
            'transaction_id' => $aggregatorResponse['transactionId'] ?? $aggregatorResponse['transaction_id'] ?? null,
            'reference' => $aggregatorResponse['reference'] ?? $aggregatorResponse['transactionRef'] ?? null,
            'amount' => $aggregatorResponse['amount'] ?? null,
            'currency' => $aggregatorResponse['currency'] ?? 'TZS',
            'customer_phone' => $aggregatorResponse['customerPhone'] ?? $aggregatorResponse['msisdn'] ?? null,
            'mobile_network' => $aggregatorResponse['mobileNetwork'] ?? $aggregatorResponse['channel'] ?? null,
            'description' => $aggregatorResponse['description'] ?? $aggregatorResponse['narration'] ?? null,
            'aggregator_status' => $aggregatorResponse['aggregatorStatus'] ?? ($status === 'failed' ? 'failed' : 'success'),
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