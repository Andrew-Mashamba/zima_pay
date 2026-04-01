<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Paths were stored as /v1/... while SELCOM_BASE_URL already includes /v1, causing /v1/v1/... 404s.
     */
    public function up(): void
    {
        $map = [
            '/v1/wallet/pushussd' => '/wallet/pushussd',
            '/v1/vendor/balance' => '/vendor/balance',
            '/v1/wallet/collection-statement' => '/wallet/collection-statement',
            '/v1/c2b/query-status' => '/c2b/query-status',
        ];

        foreach ($map as $from => $to) {
            DB::table('services')->where('endpoint', $from)->update(['endpoint' => $to]);
        }
    }

    public function down(): void
    {
        $map = [
            '/wallet/pushussd' => '/v1/wallet/pushussd',
            '/vendor/balance' => '/v1/vendor/balance',
            '/wallet/collection-statement' => '/v1/wallet/collection-statement',
            '/c2b/query-status' => '/v1/c2b/query-status',
        ];

        foreach ($map as $from => $to) {
            DB::table('services')->where('endpoint', $from)->update(['endpoint' => $to]);
        }
    }
};
