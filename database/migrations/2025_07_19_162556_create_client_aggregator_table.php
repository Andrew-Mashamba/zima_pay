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
        Schema::create('client_aggregator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('aggregator_id')->constrained()->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->text('api_credentials')->nullable();
            $table->integer('rate_limit')->nullable();
            $table->timestamps();
            
            $table->unique(['client_id', 'aggregator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_aggregator');
    }
};
