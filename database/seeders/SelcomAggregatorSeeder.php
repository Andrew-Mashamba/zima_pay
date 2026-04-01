<?php

namespace Database\Seeders;

use App\Models\Aggregator;
use App\Models\Service;
use App\Models\Client;
use App\Models\ServiceMapping;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SelcomAggregatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Selcom Wallet Pull Funds (Push USSD) - C2B/Collection Services
     * Docs: /docs/guides/wallet-pull-funds-push-ussd.md
     */
    public function run(): void
    {
        // Create or get Selcom Aggregator
        $selcom = Aggregator::firstOrCreate(
            ['code' => 'SELCOM'],
            [
                'name' => 'Selcom Mobile Money',
                'description' => 'Selcom Wallet Pull Funds (Push USSD) - C2B Collection for Tanzania',
                'api_endpoint' => env('SELCOM_BASE_URL', 'http://example.com'),
                'api_key' => env('SELCOM_API_KEY', 'your_api_key'),
                'api_secret' => env('SELCOM_API_SECRET', 'your_api_secret'),
                'status' => true,
                'rate_limit' => 1000,
                'timeout' => 30,
                'retry_attempts' => 3,
                'contact_person' => 'Selcom Support',
                'contact_email' => 'support@selcom.net',
                'settings' => [
                    'vendor' => env('SELCOM_VENDOR_ID', '01234567891'),
                    'auth_type' => 'selcom_digest',
                    'supported_wallets' => [
                        'AIRTELMONEY',
                        'MPESA-TZ',
                        'TIGOPESATZ',
                        'HALOPESATZ',
                        'TTCLMOBILE',
                        'ZANTELEZPESA',
                    ],
                    'c2b_bearer_token' => env('SELCOM_C2B_BEARER_TOKEN', ''),
                ],
            ]
        );

        // MONEY_COLLECTION - Push USSD: POST /v1/wallet/pushussd
        $moneyCollectionService = Service::updateOrCreate(
            ['code' => 'MONEY_COLLECTION', 'aggregator_id' => $selcom->id],
            [
                'name' => 'Wallet Pull Funds (Push USSD)',
                'description' => 'Trigger USSD push to customer wallet for payment collection',
                'endpoint' => '/wallet/pushussd',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 100,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => true,
                'documentation' => 'Push USSD to customer wallet. Completion notified via C2B notification.',
                'settings' => [
                    'required_fields' => ['transid', 'utilityref', 'amount', 'vendor', 'msisdn'],
                    'signed_fields' => 'transid,utilityref,amount,vendor,msisdn',
                ],
            ]
        );

        // COLLECTION_BALANCE - POST /v1/vendor/balance
        $collectionBalanceService = Service::updateOrCreate(
            ['code' => 'COLLECTION_BALANCE', 'aggregator_id' => $selcom->id],
            [
                'name' => 'Float Account Balance',
                'description' => 'Get float account balance',
                'endpoint' => '/vendor/balance',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 50,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => true,
                'documentation' => 'Get available balance from float account.',
                'settings' => [
                    'required_fields' => ['vendor', 'pin', 'transid'],
                    'signed_fields' => 'vendor,pin,transid',
                ],
            ]
        );

        // PAYMENT_STATUS - GET /v1/c2b/query-status
        $paymentStatusService = Service::updateOrCreate(
            ['code' => 'PAYMENT_STATUS', 'aggregator_id' => $selcom->id],
            [
                'name' => 'C2B Transaction Status',
                'description' => 'Query C2B transaction status',
                'endpoint' => '/c2b/query-status',
                'method' => 'GET',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 100,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => true,
                'documentation' => 'Check C2B transaction status using transid or reference.',
                'settings' => [
                    'required_fields' => ['transid', 'reference'],
                    'query_params' => true,
                ],
            ]
        );

        // Create or get Test Client
        $testClient = Client::firstOrCreate(
            ['code' => 'TEST_BANK'],
            [
                'name' => 'Test Bank',
                'description' => 'Test client for Selcom Wallet Pull Funds',
                'api_key' => 'test_bank_key_' . Str::random(16),
                'api_secret' => 'test_bank_secret_' . Str::random(32),
                'webhook_url' => 'https://webhook.site/your-unique-url',
                'status' => true,
                'contact_person' => 'Test User',
                'contact_email' => 'test@testbank.com',
                'contact_phone' => '+255123456789',
                'address' => 'Test Address, Dar es Salaam, Tanzania',
                'settings' => [
                    'default_narration' => 'Payment from Test Bank',
                ],
            ]
        );

        $testClient->aggregators()->syncWithoutDetaching([
            $selcom->id => [
                'status' => true,
                'rate_limit' => 50,
                'api_credentials' => json_encode([
                    'vendor' => $selcom->settings['vendor'] ?? env('SELCOM_VENDOR_ID', '01234567891'),
                ]),
            ],
        ]);

        $services = [$moneyCollectionService, $collectionBalanceService, $paymentStatusService];

        foreach ($services as $service) {
            $testClient->services()->syncWithoutDetaching([
                $service->id => [
                    'status' => true,
                    'rate_limit' => 50,
                    'quota' => 1000,
                ],
            ]);
        }

        $this->createServiceMappings($testClient, $selcom, $services);

        $this->command->info('Selcom Aggregator and Test Client updated successfully!');
        $this->command->info('Test Client API Key: ' . $testClient->api_key);
        $this->command->info('Test Client API Secret: ' . $testClient->api_secret);
        $this->command->info('Available Services: MONEY_COLLECTION, COLLECTION_BALANCE, PAYMENT_STATUS');
    }

    private function createServiceMappings($client, $aggregator, $services)
    {
        $mappings = [
            'MONEY_COLLECTION' => [
                'name' => 'Test Bank to Selcom Money Collection',
                'description' => 'Mapping for Test Bank to use Selcom Push USSD',
                'request_mapping' => [
                    'customer_phone' => 'msisdn',
                    'reference' => 'utilityref',
                    'amount' => 'amount',
                ],
                'response_mapping' => [
                    'result' => 'status',
                    'reference' => 'reference',
                    'transid' => 'transaction_id',
                    'resultcode' => 'result_code',
                ],
                'transformation_rules' => [],
            ],
            'COLLECTION_BALANCE' => [
                'name' => 'Test Bank to Selcom Collection Balance',
                'description' => 'Mapping for Test Bank to use Selcom Float Balance',
                'request_mapping' => [
                    'vendor' => 'vendor',
                    'pin' => 'pin',
                    'transid' => 'transid',
                ],
                'response_mapping' => [
                    'balance' => 'available_balance',
                    'resultcode' => 'status',
                ],
                'transformation_rules' => [],
            ],
            'PAYMENT_STATUS' => [
                'name' => 'Test Bank to Selcom Payment Status',
                'description' => 'Mapping for Test Bank to use Selcom C2B Query Status',
                'request_mapping' => [
                    'transaction_id' => 'transid',
                    'reference' => 'reference',
                ],
                'response_mapping' => [
                    'result' => 'status',
                    'resultcode' => 'result_code',
                    'reference' => 'reference',
                    'transid' => 'transaction_id',
                ],
                'transformation_rules' => [],
            ],
        ];

        foreach ($services as $service) {
            $mapping = $mappings[$service->code] ?? null;
            if ($mapping) {
                ServiceMapping::updateOrCreate(
                    [
                        'client_id' => $client->id,
                        'aggregator_id' => $aggregator->id,
                        'service_id' => $service->id,
                    ],
                    [
                        'name' => $mapping['name'],
                        'description' => $mapping['description'],
                        'request_mapping' => $mapping['request_mapping'],
                        'response_mapping' => $mapping['response_mapping'],
                        'transformation_rules' => $mapping['transformation_rules'],
                        'status' => true,
                        'priority' => 1,
                        'settings' => [
                            'aggregator_code' => 'SELCOM',
                        ],
                    ]
                );
            }
        }
    }
}
