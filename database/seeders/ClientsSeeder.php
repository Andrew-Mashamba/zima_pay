<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'id' => 2,
                'name' => 'Test Bank',
                'code' => 'TEST_BANK',
                'description' => 'Test client for Tembo Money Collection Service',
                'api_key' => 'test_bank_key_rXTAfjXdO1RuvICL',
                'api_secret' => 'test_bank_secret_waKb28JCEyBTfj33gTx8Fr4AFPH92Hiq',
                'webhook_url' => 'https://webhook.site/your-unique-url',
                'status' => 1,
                'contact_person' => 'Test User',
                'contact_email' => 'test@testbank.com',
                'contact_phone' => '+255123456789',
                'address' => 'Test Address, Dar es Salaam, Tanzania',
                'settings' => '{"default_channel":"TZ-AIRTEL-C2B","default_narration":"Payment from Test Bank"}',
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 11:49:12',
            ],
            [
                'id' => 3,
                'name' => 'Sample Payment Gateway',
                'code' => 'SAMPLE_PAY',
                'description' => 'Sample payment gateway for testing ZIMA ESB integration',
                'api_key' => 'sample_client_key_ABC123DEF456',
                'api_secret' => 'sample_client_secret_XYZ789GHI012',
                'webhook_url' => 'https://webhook.site/sample-client-webhook',
                'status' => 1,
                'contact_person' => 'John Doe',
                'contact_email' => 'john.doe@samplepay.com',
                'contact_phone' => '+255789123456',
                'address' => null,
                'settings' => '{"max_transaction_amount":1000000,"min_transaction_amount":100,"allowed_networks":["TZ-AIRTEL-C2B","TZ-TIGO-C2B","TZ-MPESA-C2B","TZ-HALOPESA-C2B"],"webhook_retry_attempts":3,"webhook_timeout":30,"auto_reconciliation":true,"risk_assessment_enabled":true}',
                'created_at' => '2025-07-20 11:49:19',
                'updated_at' => '2025-07-20 11:49:19',
            ],
            [
                'id' => 4,
                'name' => 'Test Multi-Items Client',
                'code' => 'TEST_MULTI_CLIENT',
                'description' => null,
                'api_key' => 'test_multi_key_1753025204',
                'api_secret' => 'test_multi_secret_1753025204',
                'webhook_url' => 'https://webhook.site/multi-items-test',
                'status' => 1,
                'contact_person' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'address' => null,
                'settings' => '{"currency":"TZS","timezone":"Africa\/Dar_es_Salaam"}',
                'created_at' => '2025-07-20 15:26:44',
                'updated_at' => '2025-07-20 15:26:44',
            ],
        ];

        // Clear existing data
        DB::table('clients')->truncate();

        // Insert new data
        foreach ($clients as $client) {
            DB::table('clients')->insert($client);
        }

        // Reset PostgreSQL sequence for clients table
        DB::statement("SELECT setval('clients_id_seq', (SELECT MAX(id) FROM clients))");

        $this->command->info('Clients seeded successfully!');
    }
}
