<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'id' => 1,
                'name' => 'Wallet Pull Funds (Push USSD)',
                'code' => 'MONEY_COLLECTION',
                'description' => 'Trigger USSD push to customer wallet for payment collection',
                'aggregator_id' => 1,
                'endpoint' => '/wallet/pushussd',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 100,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => 1,
                'settings' => json_encode([
                    'required_fields' => ['transid', 'utilityref', 'amount', 'vendor', 'msisdn'],
                    'signed_fields' => 'transid,utilityref,amount,vendor,msisdn',
                    'supported_wallets' => ['AIRTELMONEY', 'MPESA-TZ', 'TIGOPESATZ', 'HALOPESATZ', 'TTCLMOBILE', 'ZANTELEZPESA'],
                ]),
                'documentation' => 'Selcom Push USSD - Completion notified via C2B notification.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Float Account Balance',
                'code' => 'COLLECTION_BALANCE',
                'description' => 'Get float account balance',
                'aggregator_id' => 1,
                'endpoint' => '/vendor/balance',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 50,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => 1,
                'settings' => json_encode([
                    'required_fields' => ['vendor', 'pin', 'transid'],
                    'signed_fields' => 'vendor,pin,transid',
                ]),
                'documentation' => 'Get available balance from Selcom float account.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Collection Statement',
                'code' => 'COLLECTION_STATEMENT',
                'description' => 'Not supported by Selcom - kept for compatibility',
                'aggregator_id' => 1,
                'endpoint' => '/wallet/collection-statement',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 30,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => 0,
                'settings' => json_encode(['supported' => false]),
                'documentation' => 'Selcom does not provide collection statement API.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'C2B Transaction Status',
                'code' => 'PAYMENT_STATUS',
                'description' => 'Query C2B transaction status',
                'aggregator_id' => 1,
                'endpoint' => '/c2b/query-status',
                'method' => 'GET',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 100,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => 1,
                'settings' => json_encode([
                    'required_fields' => ['transid', 'reference'],
                    'signed_fields' => 'transid,reference',
                    'query_params' => true,
                ]),
                'documentation' => 'Check C2B transaction status using transid or reference.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($services as $service) {
            $row = array_merge($service, ['updated_at' => now()]);
            DB::table('services')->updateOrInsert(
                ['id' => $service['id']],
                $row
            );
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval('services_id_seq', (SELECT MAX(id) FROM services))");
        }

        $this->command->info('Services seeded successfully!');
        $this->command->info('Created ' . count($services) . ' services:');
        foreach ($services as $service) {
            $this->command->info('- ' . $service['name'] . ' (' . $service['code'] . ')');
        }
    }
}
