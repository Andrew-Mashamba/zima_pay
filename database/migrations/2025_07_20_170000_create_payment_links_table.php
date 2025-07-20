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
        Schema::create('payment_links', function (Blueprint $table) {
            $table->id();
            
            // Link identification
            $table->string('link_id')->unique()->index(); // Unique link identifier
            $table->string('short_code')->unique()->index(); // Short code for URL
            $table->string('client_reference')->index(); // Client's reference
            
            // Client and service mapping
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_mapping_id')->nullable()->constrained()->onDelete('set null');
            
            // Payment details
            $table->decimal('amount', 15, 2); // Payment amount
            $table->string('currency', 3)->default('TZS'); // Currency
            $table->string('description'); // Payment description
            $table->text('narration')->nullable(); // Detailed narration
            
            // Customer information (optional for link generation)
            $table->string('customer_phone')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            
            // Link configuration
            $table->string('payment_method')->default('mobile_money'); // Payment method
            $table->json('allowed_networks')->nullable(); // Allowed mobile networks
            $table->boolean('allow_partial_payment')->default(false); // Allow partial payments
            $table->decimal('minimum_amount', 15, 2)->nullable(); // Minimum payment amount
            $table->decimal('maximum_amount', 15, 2)->nullable(); // Maximum payment amount
            
            // Expiry and limits
            $table->timestamp('expires_at')->nullable()->index(); // Link expiry
            $table->integer('max_uses')->nullable(); // Maximum number of uses
            $table->integer('current_uses')->default(0); // Current number of uses
            
            // Status and tracking
            $table->enum('status', ['active', 'expired', 'cancelled', 'completed'])->default('active')->index();
            $table->boolean('is_reusable')->default(false); // Can be used multiple times
            $table->boolean('is_public')->default(true); // Publicly accessible
            
            // Metadata
            $table->json('metadata')->nullable(); // Additional metadata
            $table->json('settings')->nullable(); // Link settings
            
            // Webhook and notifications
            $table->string('webhook_url')->nullable(); // Webhook URL for notifications
            $table->string('success_url')->nullable(); // Success redirect URL
            $table->string('failure_url')->nullable(); // Failure redirect URL
            $table->string('cancel_url')->nullable(); // Cancel redirect URL
            
            // Analytics and tracking
            $table->integer('views_count')->default(0); // Number of times viewed
            $table->timestamp('last_viewed_at')->nullable(); // Last viewed timestamp
            $table->string('created_by')->nullable(); // Who created the link
            $table->string('updated_by')->nullable(); // Who last updated the link
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['client_id', 'status']);
            $table->index(['link_id', 'status']);
            $table->index(['expires_at', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_links');
    }
}; 