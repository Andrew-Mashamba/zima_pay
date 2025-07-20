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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('phone')->nullable()->after('is_active');
            $table->string('department')->nullable()->after('phone');
            $table->string('position')->nullable()->after('department');
            $table->string('location')->nullable()->after('position');
            $table->text('notes')->nullable()->after('location');
            $table->timestamp('last_login_at')->nullable()->after('notes');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            
            $table->index(['role', 'is_active']);
            $table->index(['department']);
            $table->index(['last_login_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'is_active']);
            $table->dropIndex(['department']);
            $table->dropIndex(['last_login_at']);
            
            $table->dropColumn([
                'role',
                'is_active',
                'phone',
                'department',
                'position',
                'location',
                'notes',
                'last_login_at',
                'last_login_ip'
            ]);
        });
    }
};