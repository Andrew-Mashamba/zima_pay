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
        // Security logs table for audit trail
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->string('endpoint')->nullable();
            $table->json('data')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['event_type', 'created_at']);
            $table->index(['client_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['severity', 'resolved', 'created_at']);
        });
        
        // IP blacklist table
        Schema::create('ip_blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->unique();
            $table->string('reason');
            $table->text('description')->nullable();
            $table->boolean('is_permanent')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->string('added_by')->nullable();
            $table->timestamps();
            
            $table->index(['ip_address']);
            $table->index(['expires_at']);
        });
        
        // IP whitelist table (for high-security clients)
        Schema::create('ip_whitelist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('ip_address');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['client_id', 'ip_address']);
            $table->unique(['client_id', 'ip_address']);
        });
        
        // API rate limiting table
        Schema::create('api_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('endpoint')->nullable(); // null for global limit
            $table->integer('requests_per_minute')->default(60);
            $table->integer('requests_per_hour')->default(3600);
            $table->integer('requests_per_day')->default(86400);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['client_id', 'endpoint']);
        });
        
        // Security incidents table
        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_type');
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->string('title');
            $table->text('description');
            $table->string('source_ip')->nullable();
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->json('affected_systems')->nullable();
            $table->json('attack_vectors')->nullable();
            $table->enum('status', ['open', 'investigating', 'mitigated', 'resolved', 'false_positive'])->default('open');
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->string('assigned_to')->nullable();
            $table->text('mitigation_steps')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->json('indicators_of_compromise')->nullable();
            $table->timestamps();
            
            $table->index(['incident_type', 'severity', 'status']);
            $table->index(['detected_at', 'severity']);
            $table->index(['source_ip']);
        });
        
        // Encryption keys table (for key rotation)
        Schema::create('encryption_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key_id')->unique();
            $table->text('encrypted_key'); // The key itself, encrypted with master key
            $table->string('algorithm')->default('AES-256-GCM');
            $table->enum('purpose', ['data', 'transport', 'signing', 'master']);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('rotated_at')->nullable();
            $table->string('rotated_by')->nullable();
            $table->timestamps();
            
            $table->index(['key_id', 'is_active']);
            $table->index(['purpose', 'is_active']);
            $table->index(['expires_at']);
        });
        
        // Failed authentication attempts
        Schema::create('failed_authentications', function (Blueprint $table) {
            $table->id();
            $table->string('api_key')->nullable();
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->string('failure_reason');
            $table->json('request_headers')->nullable();
            $table->text('request_body')->nullable();
            $table->timestamps();
            
            $table->index(['ip_address', 'created_at']);
            $table->index(['api_key', 'created_at']);
        });
        
        // Webhook security logs
        Schema::create('webhook_security_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('webhook_url');
            $table->string('request_signature');
            $table->boolean('signature_valid');
            $table->json('headers_sent')->nullable();
            $table->text('payload_hash');
            $table->integer('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->decimal('response_time', 8, 3)->nullable();
            $table->timestamps();
            
            $table->index(['client_id', 'created_at']);
            $table->index(['signature_valid', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_security_logs');
        Schema::dropIfExists('failed_authentications');
        Schema::dropIfExists('encryption_keys');
        Schema::dropIfExists('security_incidents');
        Schema::dropIfExists('api_rate_limits');
        Schema::dropIfExists('ip_whitelist');
        Schema::dropIfExists('ip_blacklist');
        Schema::dropIfExists('security_logs');
    }
};