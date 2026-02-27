<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\EsbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CallbackController extends Controller
{
    protected $esbService;

    public function __construct(EsbService $esbService)
    {
        $this->esbService = $esbService;
    }

    /**
     * Handle callback from aggregators (Tembo, etc.)
     */
    public function handleCallback(Request $request, $aggregatorCode)
    {
        $startTime = microtime(true);
        
        Log::info('Callback received', [
            'aggregator' => $aggregatorCode,
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'ip' => $request->ip(),
        ]);

        try {
            // Validate aggregator
            $aggregator = \App\Models\Aggregator::where('code', strtoupper($aggregatorCode))->first();
            if (!$aggregator) {
                Log::error('Invalid aggregator code in callback', ['code' => $aggregatorCode]);
                return response()->json(['error' => 'Invalid aggregator'], 400);
            }

            // Validate callback signature if required
            if (!$this->validateCallbackSignature($request, $aggregator)) {
                Log::error('Invalid callback signature', ['aggregator' => $aggregatorCode]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            // Extract transaction reference from callback
            $transactionRef = $this->extractTransactionReference($request, $aggregator);
            if (!$transactionRef) {
                Log::error('No transaction reference found in callback', [
                    'aggregator' => $aggregatorCode,
                    'data' => $request->all()
                ]);
                return response()->json(['error' => 'No transaction reference'], 400);
            }

            // Find the transaction
            $transaction = Transaction::where('aggregator_reference', $transactionRef)
                                    ->orWhere('external_transaction_id', $transactionRef)
                                    ->orWhere('client_reference', $transactionRef)
                                    ->orWhere('transaction_id', $transactionRef)
                                    ->first();

            if (!$transaction) {
                Log::error('Transaction not found for callback', [
                    'aggregator' => $aggregatorCode,
                    'reference' => $transactionRef
                ]);
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            // Update transaction with callback data
            $this->updateTransactionFromCallback($transaction, $request, $aggregator);

            // Send webhook notification to client if configured
            if ($transaction->webhook_url) {
                $this->esbService->sendWebhookNotification($transaction);
            }

            $processingTime = microtime(true) - $startTime;
            
            Log::info('Callback processed successfully', [
                'aggregator' => $aggregatorCode,
                'transaction_id' => $transaction->transaction_id,
                'processing_time' => $processingTime
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Callback processed successfully',
                'transaction_id' => $transaction->transaction_id
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing callback', [
                'aggregator' => $aggregatorCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => 'Callback processing failed'
            ], 500);
        }
    }

    /**
     * Validate callback signature
     */
    protected function validateCallbackSignature(Request $request, $aggregator)
    {
        // Check if aggregator requires signature validation
        if (!isset($aggregator->settings['require_signature']) || !$aggregator->settings['require_signature']) {
            return true;
        }

        $signature = $request->header('X-Signature') ?? $request->header('Signature');
        $timestamp = $request->header('X-Timestamp') ?? $request->header('Timestamp');
        
        if (!$signature || !$timestamp) {
            return false;
        }

        // Validate timestamp (prevent replay attacks)
        $timestampDiff = abs(time() - $timestamp);
        if ($timestampDiff > 300) { // 5 minutes tolerance
            return false;
        }

        // Generate expected signature
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload . $timestamp, $aggregator->api_secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Extract transaction reference from callback data
     */
    protected function extractTransactionReference(Request $request, $aggregator)
    {
        $data = $request->all();
        
        // Different aggregators use different field names
        $possibleFields = [
            'transactionRef',
            'transaction_id',
            'reference',
            'ref',
            'external_reference',
            'merchant_reference',
            'client_reference'
        ];

        foreach ($possibleFields as $field) {
            if (isset($data[$field])) {
                return $data[$field];
            }
        }

        // Check nested fields
        if (isset($data['data']['transactionRef'])) {
            return $data['data']['transactionRef'];
        }

        if (isset($data['result']['transactionRef'])) {
            return $data['result']['transactionRef'];
        }

        return null;
    }

    /**
     * Update transaction with callback data
     */
    protected function updateTransactionFromCallback($transaction, Request $request, $aggregator)
    {
        $data = $request->all();
        
        // Extract status from callback
        $status = $this->extractStatusFromCallback($data, $aggregator);
        
        // Extract additional information
        $updateData = [
            'aggregator_status' => $status,
            'aggregator_processed_at' => now(),
            'aggregator_response' => $data,
        ];

        // Extract amount if provided
        if (isset($data['amount'])) {
            $updateData['amount'] = $data['amount'];
        }

        // Extract customer information
        if (isset($data['msisdn'])) {
            $updateData['customer_phone'] = $data['msisdn'];
        }

        if (isset($data['customer_name'])) {
            $updateData['customer_name'] = $data['customer_name'];
        }

        // Extract fees and commissions
        if (isset($data['fee'])) {
            $updateData['fee_amount'] = $data['fee'];
        }

        if (isset($data['commission'])) {
            $updateData['commission_amount'] = $data['commission'];
        }

        // Extract external transaction ID
        if (isset($data['transactionId'])) {
            $updateData['external_transaction_id'] = $data['transactionId'];
        }

        // Update ESB status based on aggregator status
        $updateData['status'] = $this->mapAggregatorStatusToEsbStatus($status);
        
        // Update client status
        $updateData['client_status'] = $updateData['status'];

        // Add audit trail
        $transaction->addAuditTrail('callback_received', [
            'aggregator' => $aggregator->name,
            'status' => $status,
            'data' => $data
        ]);

        // Update the transaction
        $transaction->update($updateData);

        Log::info('Transaction updated from callback', [
            'transaction_id' => $transaction->transaction_id,
            'aggregator_status' => $status,
            'esb_status' => $updateData['status']
        ]);
    }

    /**
     * Extract status from callback data
     */
    protected function extractStatusFromCallback($data, $aggregator)
    {
        // Check common status fields
        $statusFields = ['status', 'result', 'state', 'transactionStatus'];
        
        foreach ($statusFields as $field) {
            if (isset($data[$field])) {
                $status = $data[$field];
                
                // Handle string status values
                if (is_string($status)) {
                    return strtolower($status);
                }
                
                // Handle numeric status codes
                if (is_numeric($status)) {
                    return $this->mapStatusCodeToStatus($status, $aggregator);
                }
            }
        }

        // Check nested status fields
        if (isset($data['data']['status'])) {
            return strtolower($data['data']['status']);
        }

        if (isset($data['result']['status'])) {
            return strtolower($data['result']['status']);
        }

        // Default to pending if no status found
        return 'pending';
    }

    /**
     * Map status code to status string
     */
    protected function mapStatusCodeToStatus($code, $aggregator)
    {
        // Default mapping
        $defaultMapping = [
            0 => 'pending',
            1 => 'success',
            2 => 'failed',
            3 => 'timeout',
            4 => 'cancelled'
        ];

        // Check if aggregator has custom status mapping
        if (isset($aggregator->settings['status_mapping'])) {
            $customMapping = $aggregator->settings['status_mapping'];
            if (isset($customMapping[$code])) {
                return $customMapping[$code];
            }
        }

        return $defaultMapping[$code] ?? 'pending';
    }

    /**
     * Map aggregator status to ESB status
     */
    protected function mapAggregatorStatusToEsbStatus($aggregatorStatus)
    {
        return match(strtolower($aggregatorStatus)) {
            'success', 'completed', 'approved' => 'success',
            'failed', 'rejected', 'declined' => 'failed',
            'timeout', 'expired' => 'timeout',
            'cancelled', 'aborted' => 'cancelled',
            default => 'pending'
        };
    }

    /**
     * Handle status check request
     */
    public function checkStatus(Request $request, $transactionId)
    {
        $transaction = Transaction::where('transaction_id', $transactionId)->first();
        
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        return response()->json([
            'transaction_id' => $transaction->transaction_id,
            'status' => $transaction->status,
            'aggregator_status' => $transaction->aggregator_status,
            'client_status' => $transaction->client_status,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'customer_phone' => $transaction->customer_phone,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at,
            'aggregator_processed_at' => $transaction->aggregator_processed_at,
        ]);
    }
} 