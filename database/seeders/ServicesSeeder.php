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
                'name' => 'Mobile Money Collection',
                'code' => 'MONEY_COLLECTION',
                'description' => 'Collect money from mobile subscribers through USSD push requests',
                'aggregator_id' => 1,
                'endpoint' => '/collection',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 100,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => 1,
                'settings' => json_encode([
                    'required_fields' => [
                        'msisdn',
                        'channel',
                        'amount',
                        'narration',
                        'transactionRef',
                        'transactionDate',
                        'callbackUrl'
                    ],
                    'supported_channels' => [
                        'TZ-TIGO-C2B',
                        'TZ-AIRTEL-C2B'
                    ]
                ]),
                'documentation' => 'Collect money from a mobile subscriber through a USSD push request. Supports TZ-TIGO-C2B and TZ-AIRTEL-C2B channels.',
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 11:49:12'
            ],
            [
                'id' => 2,
                'name' => 'Collection Balance',
                'code' => 'COLLECTION_BALANCE',
                'description' => 'Retrieve the balance of your collection account',
                'aggregator_id' => 1,
                'endpoint' => '/wallet/collection-balance',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 50,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => 1,
                'settings' => json_encode([
                    'required_fields' => [],
                    'response_fields' => [
                        'availableBalance',
                        'currentBalance',
                        'accountNo',
                        'accountStatus',
                        'accountName'
                    ]
                ]),
                'documentation' => 'Retrieves the balance of your collection account including available balance, current balance, account number, and account status.',
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 11:49:12'
            ],
            [
                'id' => 3,
                'name' => 'Collection Statement',
                'code' => 'COLLECTION_STATEMENT',
                'description' => 'Retrieve the statement of your collection account',
                'aggregator_id' => 1,
                'endpoint' => '/wallet/collection-statement',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 30,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => 1,
                'settings' => json_encode([
                    'required_fields' => [
                        'startDate',
                        'endDate'
                    ],
                    'response_fields' => [
                        'accountNo',
                        'debitOrCredit',
                        'tranRefNo',
                        'narration',
                        'txnDate',
                        'valueDate',
                        'amountCredited',
                        'amountDebited',
                        'balance'
                    ]
                ]),
                'documentation' => 'Check the account statement of your collection account for a specified date range.',
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 11:49:12'
            ],
            [
                'id' => 4,
                'name' => 'Payment Status',
                'code' => 'PAYMENT_STATUS',
                'description' => 'Check the current status of a USSD push transaction',
                'aggregator_id' => 1,
                'endpoint' => '/collection/status',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 100,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => 1,
                'settings' => json_encode([
                    'required_fields' => [
                        'transactionId',
                        'transactionRef'
                    ],
                    'response_fields' => [
                        'statusCode',
                        'transactionId',
                        'transactionRef'
                    ]
                ]),
                'documentation' => 'Checks the current status of a USSD push transaction using transaction ID and reference.',
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 11:49:12'
            ]
        ];

        // Insert new data
        foreach ($services as $service) {
            DB::table('services')->insert($service);
        }

        // Reset PostgreSQL sequence for services table
        DB::statement("SELECT setval('services_id_seq', (SELECT MAX(id) FROM services))");

        $this->command->info('Services seeded successfully!');
        $this->command->info('Created ' . count($services) . ' services:');
        foreach ($services as $service) {
            $this->command->info('- ' . $service['name'] . ' (' . $service['code'] . ')');
        }
    }
}
