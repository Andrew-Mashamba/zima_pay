<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\PaymentLink;
use App\Services\EsbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Selcom C2B/Collection endpoints.
 * Selcom calls these when using Push USSD - lookup, validation, notification.
 * Docs: SELCOMAPIS/docs/08-c2b-collection.md
 */
class SelcomC2bController extends Controller
{
    public function __construct(protected EsbService $esbService)
    {
    }

    protected function validateC2bAuth(Request $request): bool
    {
        $token = env('SELCOM_C2B_BEARER_TOKEN');
        if (empty($token)) {
            return true;
        }
        $auth = $request->header('Authorization');
        return $auth === 'Bearer ' . $token;
    }

    /**
     * Lookup - verify amount, utilityref, phone before payment
     */
    public function lookup(Request $request)
    {
        if (!$this->validateC2bAuth($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        Log::info('Selcom C2B Lookup', ['data' => $request->all()]);

        $utilityref = $request->input('utilityref');
        $msisdn = $request->input('msisdn');
        $transid = $request->input('transid');
        $reference = $request->input('reference');

        $transaction = $this->findTransaction($utilityref, $transid, $reference);
        if (!$transaction) {
            return $this->c2bResponse($reference ?? $transid, '010', 'FAIL', 'Invalid account or payment reference (utilityref)');
        }

        return $this->c2bResponse($reference ?? $transid, '000', 'SUCCESS', 'OK', [
            'name' => $transaction->customer_name ?? 'Customer',
            'amount' => (int) $transaction->amount,
        ]);
    }

    /**
     * Validation - verify before funds are pulled (timeout/failure = auto reverse)
     */
    public function validation(Request $request)
    {
        if (!$this->validateC2bAuth($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        Log::info('Selcom C2B Validation', ['data' => $request->all()]);

        $utilityref = $request->input('utilityref');
        $amount = (int) $request->input('amount');
        $msisdn = $request->input('msisdn');
        $reference = $request->input('reference');
        $transid = $request->input('transid');

        $transaction = $this->findTransaction($utilityref, $transid, $reference);
        if (!$transaction) {
            return $this->c2bResponse($reference ?? $transid, '010', 'FAIL', 'Invalid account or payment reference');
        }

        if ($amount <= 0) {
            return $this->c2bResponse($reference ?? $transid, '012', 'FAIL', 'Invalid amount');
        }

        if (abs($amount - (int) $transaction->amount) > 1) {
            return $this->c2bResponse($reference ?? $transid, '012', 'FAIL', 'Amount mismatch');
        }

        return $this->c2bResponse($reference ?? $transid, '000', 'SUCCESS', 'Validation successful', [
            'name' => $transaction->customer_name ?? 'Customer',
        ]);
    }

    /**
     * Notification - payment confirmation (final callback)
     */
    public function notification(Request $request)
    {
        if (!$this->validateC2bAuth($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        Log::info('Selcom C2B Notification', ['data' => $request->all()]);

        $utilityref = $request->input('utilityref');
        $reference = $request->input('reference');
        $transid = $request->input('transid');
        $amount = $request->input('amount');
        $msisdn = $request->input('msisdn');
        $operator = $request->input('operator');
        $resultcode = $request->input('resultcode', '000');

        $transaction = $this->findTransaction($utilityref, $transid, $reference);
        if (!$transaction) {
            Log::warning('Selcom notification: transaction not found', [
                'utilityref' => $utilityref,
                'reference' => $reference,
                'transid' => $transid,
            ]);
            return $this->c2bResponse($reference ?? $transid, '010', 'FAIL', 'Transaction not found');
        }

        $status = $resultcode === '000' ? 'success' : 'failed';
        $aggregatorStatus = $resultcode === '000' ? 'success' : 'failed';
        $transaction->update([
            'status' => $status,
            'aggregator_status' => $aggregatorStatus,
            'aggregator_processed_at' => now(),
            'aggregator_response' => $request->all(),
            'external_transaction_id' => $reference ?? $transid,
            'amount' => $amount ?? $transaction->amount,
            'customer_phone' => $msisdn ?? $transaction->customer_phone,
        ]);

        if ($status === 'success') {
            $this->updatePaymentLinkFromNotification($transaction);
        }

        if ($transaction->webhook_url) {
            $this->esbService->sendWebhookNotification($transaction);
        }

        return $this->c2bResponse($reference ?? $transid, '000', 'SUCCESS', 'Notification received');
    }

    /**
     * Update payment_link_items and payment_links when C2B notification confirms success.
     * Idempotent: skips if already applied (e.g. processUniversalPayment may have run first).
     */
    protected function updatePaymentLinkFromNotification(Transaction $transaction): void
    {
        $metadata = $transaction->request_data['metadata'] ?? $transaction->metadata ?? [];
        if ($metadata['payment_link_updated'] ?? false) {
            return;
        }

        $shortCode = $metadata['payment_link_short_code'] ?? null;
        $itemizedPayments = $metadata['itemized_payments'] ?? [];

        if (!$shortCode || empty($itemizedPayments)) {
            return;
        }

        $paymentLink = PaymentLink::where('short_code', $shortCode)->first();
        if (!$paymentLink) {
            return;
        }

        foreach ($itemizedPayments as $itemPayment) {
            $itemCode = $itemPayment['item_code'] ?? null;
            $amount = (float) ($itemPayment['amount'] ?? 0);
            if (!$itemCode || $amount <= 0) {
                continue;
            }

            $item = $paymentLink->items()->where('item_code', $itemCode)->first();
            if ($item) {
                $item->recordPayment($amount);
                Log::info('C2B notification: recorded payment for item', [
                    'item_code' => $itemCode,
                    'amount' => $amount,
                ]);
            }
        }

        $paymentLink->incrementUses();
        Log::info('C2B notification: incremented payment link uses', [
            'short_code' => $shortCode,
        ]);

        $meta = $transaction->metadata ?? [];
        $meta['payment_link_updated'] = true;
        $transaction->update(['metadata' => $meta]);
    }

    protected function findTransaction(?string $utilityref, ?string $transid, ?string $reference): ?Transaction
    {
        $searchTerms = array_filter([$utilityref, $transid, $reference]);
        if (empty($searchTerms)) {
            return null;
        }

        return Transaction::where(function ($q) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $q->orWhere('client_reference', $term)
                    ->orWhere('transaction_id', $term)
                    ->orWhere('aggregator_reference', $term)
                    ->orWhere('external_transaction_id', $term);
            }
        })->first();
    }

    protected function c2bResponse(string $reference, string $resultcode, string $result, string $message, array $extra = []): \Illuminate\Http\JsonResponse
    {
        return response()->json(array_merge([
            'reference' => $reference,
            'resultcode' => $resultcode,
            'result' => $result,
            'message' => $message,
        ], $extra));
    }
}
