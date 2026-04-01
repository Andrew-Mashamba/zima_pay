<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AggregatorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aggregators = [
            [
                'id' => 1,
                'name' => 'Selcom Mobile Money',
                'code' => 'SELCOM',
                'description' => 'Selcom Wallet Pull Funds (Push USSD) - C2B Collection for Tanzania',
                'api_endpoint' => env('SELCOM_BASE_URL', 'http://example.com'),
                'api_key' => env('SELCOM_API_KEY', 'your_api_key'),
                'api_secret' => env('SELCOM_API_SECRET', 'your_api_secret'),
                'webhook_url' => null,
                'status' => 1,
                'rate_limit' => 1000,
                'timeout' => 30,
                'retry_attempts' => 3,
                'contact_person' => 'Selcom Support',
                'contact_email' => 'support@selcom.net',
                'contact_phone' => null,
                'settings' => json_encode([
                    'vendor' => env('SELCOM_VENDOR_ID', '01234567891'),
                    'auth_type' => 'selcom_digest',
                    'supported_wallets' => ['AIRTELMONEY', 'MPESA-TZ', 'TIGOPESATZ', 'HALOPESATZ', 'TTCLMOBILE', 'ZANTELEZPESA'],
                    'c2b_bearer_token' => env('SELCOM_C2B_BEARER_TOKEN', ''),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Clear existing data (disable FK checks so truncate works when other tables reference aggregators)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('aggregators')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE aggregators CASCADE');
        } else {
            DB::table('aggregators')->truncate();
        }

        // Insert new data
        foreach ($aggregators as $aggregator) {
            DB::table('aggregators')->insert($aggregator);
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval('aggregators_id_seq', (SELECT MAX(id) FROM aggregators))");
        }

        $this->command->info('Aggregators seeded successfully!');
    }
}
