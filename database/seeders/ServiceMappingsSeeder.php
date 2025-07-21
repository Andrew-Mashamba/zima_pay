<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceMappingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceMappings = [
            [
                'id' => 1,
                'name' => 'Test Bank to Tembo Money Collection',
                'description' => 'Mapping for Test Bank to use Tembo Money Collection Service',
                'client_id' => 2,
                'aggregator_id' => 1,
                'service_id' => 1,
                'request_mapping' => '{"customer_phone":"msisdn","mobile_network":"channel","amount":"amount","description":"narration","reference":"transactionRef","date":"transactionDate","webhook_url":"callbackUrl"}',
                'response_mapping' => '{"statusCode":"status","transactionRef":"reference","transactionId":"transaction_id"}',
                'transformation_rules' => '[{"type":"format_date","field":"transactionDate","format":"Y-m-d H:i:s"},{"type":"uppercase","field":"mobile_network"}]',
                'status' => 1,
                'priority' => 1,
                'settings' => '{"default_channel":"TZ-AIRTEL-C2B","default_narration":"Payment from Test Bank"}',
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 11:49:12',
            ],
            [
                'id' => 2,
                'name' => 'Test Bank to Tembo Collection Balance',
                'description' => 'Mapping for Test Bank to use Tembo Collection Balance Service',
                'client_id' => 2,
                'aggregator_id' => 1,
                'service_id' => 2,
                'request_mapping' => '[]',
                'response_mapping' => '{"availableBalance":"available_balance","currentBalance":"current_balance","accountNo":"account_number","accountStatus":"account_status","accountName":"account_name"}',
                'transformation_rules' => '[]',
                'status' => 1,
                'priority' => 1,
                'settings' => '{"default_channel":"TZ-AIRTEL-C2B","default_narration":"Payment from Test Bank"}',
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 11:49:12',
            ],
            [
                'id' => 3,
                'name' => 'Test Bank to Tembo Collection Statement',
                'description' => 'Mapping for Test Bank to use Tembo Collection Statement Service',
                'client_id' => 2,
                'aggregator_id' => 1,
                'service_id' => 3,
                'request_mapping' => '{"start_date":"startDate","end_date":"endDate"}',
                'response_mapping' => '{"accountNo":"account_number","debitOrCredit":"transaction_type","tranRefNo":"transaction_reference","narration":"description","txnDate":"transaction_date","valueDate":"value_date","amountCredited":"amount_credited","amountDebited":"amount_debited","balance":"balance"}',
                'transformation_rules' => '[{"type":"format_date","field":"startDate","format":"Y-m-d"},{"type":"format_date","field":"endDate","format":"Y-m-d"}]',
                'status' => 1,
                'priority' => 1,
                'settings' => '{"default_channel":"TZ-AIRTEL-C2B","default_narration":"Payment from Test Bank"}',
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 11:49:12',
            ],
            [
                'id' => 4,
                'name' => 'Test Bank to Tembo Payment Status',
                'description' => 'Mapping for Test Bank to use Tembo Payment Status Service',
                'client_id' => 2,
                'aggregator_id' => 1,
                'service_id' => 4,
                'request_mapping' => '{"transaction_id":"transactionId","reference":"transactionRef"}',
                'response_mapping' => '{"statusCode":"status","transactionId":"transaction_id","transactionRef":"reference"}',
                'transformation_rules' => '[]',
                'status' => 1,
                'priority' => 1,
                'settings' => '{"default_channel":"TZ-AIRTEL-C2B","default_narration":"Payment from Test Bank"}',
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 11:49:12',
            ],
            [
                'id' => 5,
                'name' => 'Sample Payment Gateway - Money Collection',
                'description' => 'Service mapping for Sample Payment Gateway money collection',
                'client_id' => 3,
                'aggregator_id' => 1,
                'service_id' => 1,
                'request_mapping' => '{"customer_phone":"customer_phone","mobile_network":"mobile_network","amount":"amount","description":"description","reference":"reference","date":"date","webhook_url":"webhook_url"}',
                'response_mapping' => '{"transaction_id":"transaction_id","reference":"reference","status":"status","message":"message"}',
                'transformation_rules' => null,
                'status' => 1,
                'priority' => 0,
                'settings' => '{"commission_rate":2.5,"transaction_fee":50,"daily_limit":10000000,"monthly_limit":100000000,"allowed_amount_ranges":{"small":{"min":100,"max":1000},"medium":{"min":1001,"max":50000},"large":{"min":50001,"max":1000000}},"webhook_events":["transaction.created","transaction.completed","transaction.failed"],"retry_config":{"max_attempts":3,"delay_seconds":[5,15,60]}}',
                'created_at' => '2025-07-20 11:49:19',
                'updated_at' => '2025-07-20 11:49:19',
            ],
            [
                'id' => 8,
                'name' => 'Test Multi-Items Collection Mapping',
                'description' => 'Service mapping for multiple items payment testing',
                'client_id' => 4,
                'aggregator_id' => 1,
                'service_id' => 1,
                'request_mapping' => '{"customer_phone":"msisdn","mobile_network":"channel","amount":"amount","reference":"reference","description":"narration","date":"transactionDate","webhook_url":"callbackUrl"}',
                'response_mapping' => '{"status_code":"status","transaction_id":"transactionId","message":"message"}',
                'transformation_rules' => null,
                'status' => 1,
                'priority' => 0,
                'settings' => '{"timeout":30,"retry_attempts":3}',
                'created_at' => '2025-07-20 15:28:03',
                'updated_at' => '2025-07-20 15:28:03',
            ],
        ];

        // Clear existing data
        DB::table('service_mappings')->truncate();

        // Insert new data
        foreach ($serviceMappings as $mapping) {
            DB::table('service_mappings')->insert($mapping);
        }

        // Reset PostgreSQL sequence for service_mappings table
        DB::statement("SELECT setval('service_mappings_id_seq', (SELECT MAX(id) FROM service_mappings))");

        $this->command->info('Service Mappings seeded successfully!');
    }
}
