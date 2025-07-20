<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Enhanced transaction identification
            $table->string('external_transaction_id')->nullable()->after('transaction_id');
            $table->string('aggregator_reference')->nullable()->after('external_transaction_id');
            $table->string('client_reference')->nullable()->after('aggregator_reference');
            
            // Financial details
            $table->decimal('amount', 15, 2)->nullable()->after('client_reference');
            $table->string('currency', 3)->default('TZS')->after('amount');
            $table->decimal('fee_amount', 10, 2)->nullable()->after('currency');
            $table->decimal('commission_amount', 10, 2)->nullable()->after('fee_amount');
            
            // Customer details
            $table->string('customer_phone')->nullable()->after('commission_amount');
            $table->string('customer_name')->nullable()->after('customer_phone');
            $table->string('mobile_network')->nullable()->after('customer_name');
            $table->string('network_provider')->nullable()->after('mobile_network');
            
            // Transaction details
            $table->text('description')->nullable()->after('network_provider');
            $table->string('narration')->nullable()->after('description');
            $table->string('channel')->nullable()->after('narration');
            $table->string('payment_method')->nullable()->after('channel');
            
            // Request/Response details
            $table->json('original_request')->nullable()->after('request_data');
            $table->json('transformed_request')->nullable()->after('original_request');
            $table->json('aggregator_request')->nullable()->after('transformed_request');
            $table->json('aggregator_response')->nullable()->after('aggregator_request');
            $table->json('transformed_response')->nullable()->after('aggregator_response');
            $table->json('final_response')->nullable()->after('transformed_response');
            
            // Status tracking
            $table->enum('aggregator_status', ['pending', 'success', 'failed', 'timeout', 'cancelled'])->nullable()->after('status');
            $table->enum('client_status', ['pending', 'success', 'failed', 'timeout', 'cancelled'])->nullable()->after('aggregator_status');
            $table->timestamp('aggregator_processed_at')->nullable()->after('client_status');
            $table->timestamp('client_notified_at')->nullable()->after('aggregator_processed_at');
            
            // Performance metrics
            $table->float('esb_processing_time')->nullable()->after('response_time');
            $table->float('aggregator_response_time')->nullable()->after('esb_processing_time');
            $table->float('total_processing_time')->nullable()->after('aggregator_response_time');
            $table->integer('retry_count')->default(0)->after('total_processing_time');
            $table->timestamp('last_retry_at')->nullable()->after('retry_count');
            
            // Security and compliance
            $table->string('request_signature')->nullable()->after('last_retry_at');
            $table->string('response_signature')->nullable()->after('request_signature');
            $table->string('checksum')->nullable()->after('response_signature');
            $table->boolean('is_encrypted')->default(false)->after('checksum');
            
            // Webhook and notifications
            $table->string('webhook_url')->nullable()->after('is_encrypted');
            $table->integer('webhook_attempts')->default(0)->after('webhook_url');
            $table->timestamp('last_webhook_sent_at')->nullable()->after('webhook_attempts');
            $table->json('webhook_responses')->nullable()->after('last_webhook_sent_at');
            
            // Reconciliation fields
            $table->boolean('is_reconciled')->default(false)->after('webhook_responses');
            $table->timestamp('reconciled_at')->nullable()->after('is_reconciled');
            $table->string('reconciled_by')->nullable()->after('reconciled_at');
            $table->text('reconciliation_notes')->nullable()->after('reconciled_by');
            
            // Audit fields
            $table->string('created_by')->nullable()->after('reconciliation_notes');
            $table->string('updated_by')->nullable()->after('created_by');
            $table->json('audit_trail')->nullable()->after('updated_by');
            
            // Additional metadata
            $table->string('session_id')->nullable()->after('audit_trail');
            $table->string('correlation_id')->nullable()->after('session_id');
            $table->string('trace_id')->nullable()->after('correlation_id');
            $table->json('tags')->nullable()->after('trace_id');
            
            // Risk and compliance
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('low')->after('tags');
            $table->boolean('is_suspicious')->default(false)->after('risk_level');
            $table->text('risk_notes')->nullable()->after('is_suspicious');
            $table->boolean('requires_manual_review')->default(false)->after('risk_notes');
            
            // Settlement fields
            $table->boolean('is_settled')->default(false)->after('requires_manual_review');
            $table->timestamp('settled_at')->nullable()->after('is_settled');
            $table->string('settlement_reference')->nullable()->after('settled_at');
            $table->decimal('settlement_amount', 15, 2)->nullable()->after('settlement_reference');
            
            // Add indexes for performance
            $table->index(['external_transaction_id']);
            $table->index(['aggregator_reference']);
            $table->index(['client_reference']);
            $table->index(['customer_phone']);
            $table->index(['amount', 'created_at']);
            $table->index(['aggregator_status', 'created_at']);
            $table->index(['is_reconciled', 'created_at']);
            $table->index(['is_settled', 'created_at']);
            $table->index(['risk_level', 'created_at']);
            $table->index(['webhook_url', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop all the new columns
            $columns = [
                'external_transaction_id', 'aggregator_reference', 'client_reference',
                'amount', 'currency', 'fee_amount', 'commission_amount',
                'customer_phone', 'customer_name', 'mobile_network', 'network_provider',
                'description', 'narration', 'channel', 'payment_method',
                'original_request', 'transformed_request', 'aggregator_request',
                'aggregator_response', 'transformed_response', 'final_response',
                'aggregator_status', 'client_status', 'aggregator_processed_at', 'client_notified_at',
                'esb_processing_time', 'aggregator_response_time', 'total_processing_time',
                'retry_count', 'last_retry_at',
                'request_signature', 'response_signature', 'checksum', 'is_encrypted',
                'webhook_url', 'webhook_attempts', 'last_webhook_sent_at', 'webhook_responses',
                'is_reconciled', 'reconciled_at', 'reconciled_by', 'reconciliation_notes',
                'created_by', 'updated_by', 'audit_trail',
                'session_id', 'correlation_id', 'trace_id', 'tags',
                'risk_level', 'is_suspicious', 'risk_notes', 'requires_manual_review',
                'is_settled', 'settled_at', 'settlement_reference', 'settlement_amount'
            ];
            
            foreach ($columns as $column) {
                $table->dropColumn($column);
            }
            
            // Drop indexes
            $indexes = [
                'transactions_external_transaction_id_index',
                'transactions_aggregator_reference_index',
                'transactions_client_reference_index',
                'transactions_customer_phone_index',
                'transactions_amount_created_at_index',
                'transactions_aggregator_status_created_at_index',
                'transactions_is_reconciled_created_at_index',
                'transactions_is_settled_created_at_index',
                'transactions_risk_level_created_at_index',
                'transactions_webhook_url_created_at_index'
            ];
            
            foreach ($indexes as $index) {
                $table->dropIndex($index);
            }
        });
    }
}; 