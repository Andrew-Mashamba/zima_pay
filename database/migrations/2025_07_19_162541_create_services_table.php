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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->foreignId('aggregator_id')->constrained()->onDelete('cascade');
            $table->string('endpoint');
            $table->string('method')->default('POST');
            $table->string('request_format')->default('json');
            $table->string('response_format')->default('json');
            $table->integer('rate_limit')->default(100);
            $table->integer('timeout')->default(30);
            $table->integer('retry_attempts')->default(3);
            $table->boolean('status')->default(true);
            $table->json('settings')->nullable();
            $table->text('documentation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
