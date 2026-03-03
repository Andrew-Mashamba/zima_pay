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
                'name' => 'Test Bank to Selcom Money Collection',
                'description' => 'Mapping for Test Bank to use Selcom Push USSD',
                'client_id' => 2,
                'aggregator_id' => 1,
                'service_id' => 1,
                'request_mapping' => '{"customer_phone":"msisdn","reference":"utilityref","amount":"amount"}',
                'response_mapping' => '{"result":"status","reference":"reference","transid":"transaction_id","resultcode":"result_code"}',
                'transformation_rules' => '[]',
                'status' => 1,
                'priority' => 1,
                'settings' => '{"aggregator_code":"SELCOM","default_narration":"Payment from Test Bank"}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Test Bank to Selcom Collection Balance',
                'description' => 'Mapping for Test Bank to use Selcom Float Balance',
                'client_id' => 2,
                'aggregator_id' => 1,
                'service_id' => 2,
                'request_mapping' => '{"vendor":"vendor","pin":"pin","transid":"transid"}',
                'response_mapping' => '{"balance":"available_balance","resultcode":"status"}',
                'transformation_rules' => '[]',
                'status' => 1,
                'priority' => 1,
                'settings' => '{"aggregator_code":"SELCOM"}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Test Bank to Selcom Collection Statement',
                'description' => 'Not supported by Selcom - kept for compatibility',
                'client_id' => 2,
                'aggregator_id' => 1,
                'service_id' => 3,
                'request_mapping' => '{}',
                'response_mapping' => '{}',
                'transformation_rules' => '[]',
                'status' => 0,
                'priority' => 1,
                'settings' => '{"aggregator_code":"SELCOM","supported":false}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Test Bank to Selcom Payment Status',
                'description' => 'Mapping for Test Bank to use Selcom C2B Query Status',
                'client_id' => 2,
                'aggregator_id' => 1,
                'service_id' => 4,
                'request_mapping' => '{"transaction_id":"transid","reference":"reference"}',
                'response_mapping' => '{"result":"status","resultcode":"result_code","reference":"reference","transid":"transaction_id"}',
                'transformation_rules' => '[]',
                'status' => 1,
                'priority' => 1,
                'settings' => '{"aggregator_code":"SELCOM"}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Sample Payment Gateway - Money Collection',
                'description' => 'Service mapping for Sample Payment Gateway money collection',
                'client_id' => 3,
                'aggregator_id' => 1,
                'service_id' => 1,
                'request_mapping' => '{"customer_phone":"msisdn","reference":"utilityref","amount":"amount"}',
                'response_mapping' => '{"transaction_id":"transid","reference":"reference","status":"result","message":"message"}',
                'transformation_rules' => null,
                'status' => 1,
                'priority' => 0,
                'settings' => '{"aggregator_code":"SELCOM","commission_rate":2.5,"transaction_fee":50}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'Test Multi-Items Collection Mapping',
                'description' => 'Service mapping for multiple items payment testing',
                'client_id' => 4,
                'aggregator_id' => 1,
                'service_id' => 1,
                'request_mapping' => '{"customer_phone":"msisdn","reference":"utilityref","amount":"amount"}',
                'response_mapping' => '{"status":"result","transaction_id":"transid","message":"message"}',
                'transformation_rules' => null,
                'status' => 1,
                'priority' => 0,
                'settings' => '{"aggregator_code":"SELCOM","timeout":30,"retry_attempts":3}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Clear existing data
        DB::table('service_mappings')->truncate();

        // Insert new data
        foreach ($serviceMappings as $mapping) {
            DB::table('service_mappings')->insert($mapping);
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval('service_mappings_id_seq', (SELECT MAX(id) FROM service_mappings))");
        }

        $this->command->info('Service Mappings seeded successfully!');
    }
}
