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
        Schema::create('payment_link_items', function (Blueprint $table) {
            $table->id();
            
            // Payment link relationship
            $table->foreignId('payment_link_id')->constrained()->onDelete('cascade');
            
            // Item details
            $table->string('item_code')->index(); // Unique item identifier
            $table->string('item_name'); // Item name (e.g., "School Fees", "Uniform")
            $table->text('item_description')->nullable(); // Detailed description
            $table->decimal('amount', 15, 2); // Item amount
            $table->decimal('paid_amount', 15, 2)->default(0); // Amount paid for this item
            $table->string('currency', 3)->default('TZS'); // Currency
            
            // Item configuration
            $table->boolean('is_required')->default(true); // Must be paid
            $table->boolean('allow_partial')->default(false); // Allow partial payment for this item
            $table->decimal('minimum_amount', 15, 2)->nullable(); // Minimum payment for this item
            $table->integer('quantity')->default(1); // Quantity (for multiple items)
            $table->string('unit')->nullable(); // Unit (e.g., "per semester", "per piece")
            
            // Item metadata
            $table->json('metadata')->nullable(); // Additional item data
            $table->string('category')->nullable()->index(); // Item category
            $table->string('subcategory')->nullable(); // Item subcategory
            
            // Status tracking
            $table->enum('status', ['pending', 'partial', 'paid', 'cancelled'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable(); // When item was fully paid
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['payment_link_id', 'status']);
            $table->index(['item_code', 'status']);
            $table->index(['category', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_link_items');
    }
}; 