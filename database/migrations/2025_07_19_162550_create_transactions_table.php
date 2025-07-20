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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('aggregator_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_mapping_id')->nullable()->constrained()->onDelete('set null');
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->enum('status', ['pending', 'success', 'failed', 'timeout'])->default('pending');
            $table->text('error_message')->nullable();
            $table->float('response_time')->nullable();
            $table->integer('request_size')->nullable();
            $table->integer('response_size')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['client_id', 'created_at']);
            $table->index(['aggregator_id', 'created_at']);
            $table->index(['service_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
