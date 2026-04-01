<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow the same logical service code (e.g. MONEY_COLLECTION) per aggregator
     * (Selcom vs Tembo), instead of a single global unique on code.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->unique(['code', 'aggregator_id'], 'services_code_aggregator_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique('services_code_aggregator_id_unique');
            $table->unique('code');
        });
    }
};
