<?php

namespace Database\Seeders;

use App\Models\Aggregator;
use App\Models\Service;
use App\Models\Client;
use App\Models\ServiceMapping;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TemboAggregatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tembo credentials: set TEMBO_* in .env, then run this seeder (or config:clear).
        $temboAttrs = [
            'name' => 'Tembo Plus',
            'description' => 'Tembo Plus Mobile Money Collection Service for Tanzania',
            'api_endpoint' => config('services.tembo.api_base_url'),
            'status' => true,
            'rate_limit' => 1000,
            'timeout' => 30,
            'retry_attempts' => 3,
            'contact_person' => 'Tembo Support',
            'contact_email' => 'support@temboplus.com',
            'settings' => [
                'headers' => [
                    'content-type' => 'application/json',
                ],
                'supported_channels' => [
                    'TZ-TIGO-C2B',
                    'TZ-AIRTEL-C2B',
                    'TZ-VODACOM-C2B',
                    'TZ-MPESA-C2B',
                ],
            ],
        ];
        if (filled(config('services.tembo.account_id'))) {
            $temboAttrs['api_key'] = config('services.tembo.account_id');
        }
        if (filled(config('services.tembo.secret_key'))) {
            $temboAttrs['api_secret'] = config('services.tembo.secret_key');
        }

        $tembo = Aggregator::updateOrCreate(
            ['code' => 'TEMBO'],
            $temboAttrs
        );

        // Create or update Money Collection Service
        $moneyCollectionService = Service::updateOrCreate(
            ['code' => 'MONEY_COLLECTION', 'aggregator_id' => $tembo->id],
            [
                'name' => 'Mobile Money Collection',
                'description' => 'Collect money from mobile subscribers through USSD push requests',
                'endpoint' => '/collection',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 100,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => true,
                'documentation' => 'Collect money from a mobile subscriber through a USSD push request. Supports TZ-TIGO-C2B and TZ-AIRTEL-C2B channels.',
                'settings' => [
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
                ]
            ]
        );

        // Create or update Collection Balance Service
        $collectionBalanceService = Service::updateOrCreate(
            ['code' => 'COLLECTION_BALANCE', 'aggregator_id' => $tembo->id],
            [
                'name' => 'Collection Balance',
                'description' => 'Retrieve the balance of your collection account',
                'endpoint' => '/wallet/collection-balance',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 50,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => true,
                'documentation' => 'Retrieves the balance of your collection account including available balance, current balance, account number, and account status.',
                'settings' => [
                    'required_fields' => [],
                    'response_fields' => [
                        'availableBalance',
                        'currentBalance',
                        'accountNo',
                        'accountStatus',
                        'accountName'
                    ]
                ]
            ]
        );

        // Create or update Collection Statement Service
        $collectionStatementService = Service::updateOrCreate(
            ['code' => 'COLLECTION_STATEMENT', 'aggregator_id' => $tembo->id],
            [
                'name' => 'Collection Statement',
                'description' => 'Retrieve the statement of your collection account',
                'endpoint' => '/wallet/collection-statement',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 30,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => true,
                'documentation' => 'Check the account statement of your collection account for a specified date range.',
                'settings' => [
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
                ]
            ]
        );

        // Create or update Payment Status Service
        $paymentStatusService = Service::updateOrCreate(
            ['code' => 'PAYMENT_STATUS', 'aggregator_id' => $tembo->id],
            [
                'name' => 'Payment Status',
                'description' => 'Check the current status of a USSD push transaction',
                'endpoint' => '/collection/status',
                'method' => 'POST',
                'request_format' => 'json',
                'response_format' => 'json',
                'rate_limit' => 100,
                'timeout' => 30,
                'retry_attempts' => 3,
                'status' => true,
                'documentation' => 'Checks the current status of a USSD push transaction using transaction ID and reference.',
                'settings' => [
                    'required_fields' => [
                        'transactionId',
                        'transactionRef'
                    ],
                    'response_fields' => [
                        'statusCode',
                        'transactionId',
                        'transactionRef'
                    ]
                ]
            ]
        );

        // Create or get Test Client
        $testClient = Client::firstOrCreate(
            ['code' => 'TEST_BANK'],
            [
                'name' => 'Test Bank',
                'description' => 'Test client for Tembo Money Collection Service',
                'api_key' => 'test_bank_key_' . Str::random(16),
                'api_secret' => 'test_bank_secret_' . Str::random(32),
                'webhook_url' => 'https://webhook.site/your-unique-url',
                'status' => true,
                'contact_person' => 'Test User',
                'contact_email' => 'test@testbank.com',
                'contact_phone' => '+255123456789',
                'address' => 'Test Address, Dar es Salaam, Tanzania',
                'settings' => [
                    'default_channel' => 'TZ-AIRTEL-C2B',
                    'default_narration' => 'Payment from Test Bank'
                ]
            ]
        );

        // Sync client to aggregator
        $testClient->aggregators()->syncWithoutDetaching([
            $tembo->id => [
                'status' => true,
                'rate_limit' => 50,
                            'api_credentials' => json_encode([
                'account_id' => 'bf71ba501b37d989db6224fd',
                'secret_key' => 'vd//lqSw67Nl08e7Y2YzWcs2EL+rAIImpl4U4uNHdQg='
            ])
            ]
        ]);

        // Sync client to all services
        $services = [$moneyCollectionService, $collectionBalanceService, $collectionStatementService, $paymentStatusService];
        
        foreach ($services as $service) {
            $testClient->services()->syncWithoutDetaching([
                $service->id => [
                    'status' => true,
                    'rate_limit' => 50,
                    'quota' => 1000
                ]
            ]);
        }

        // Create or update Service Mappings for all services
        $this->createServiceMappings($testClient, $tembo, $services);

        $this->command->info('Tembo Aggregator and Test Client updated successfully!');
        $this->command->info('Test Client API Key: ' . $testClient->api_key);
        $this->command->info('Test Client API Secret: ' . $testClient->api_secret);
        $this->command->info('Available Services: MONEY_COLLECTION, COLLECTION_BALANCE, COLLECTION_STATEMENT, PAYMENT_STATUS');
    }

    /**
     * Create service mappings for all services
     */
    private function createServiceMappings($client, $aggregator, $services)
    {
        $mappings = [
            'MONEY_COLLECTION' => [
                'name' => 'Test Bank to Tembo Money Collection',
                'description' => 'Mapping for Test Bank to use Tembo Money Collection Service',
                'request_mapping' => [
                    'customer_phone' => 'msisdn',
                    'mobile_network' => 'channel',
                    'amount' => 'amount',
                    'description' => 'narration',
                    'reference' => 'transactionRef',
                    'date' => 'transactionDate',
                    'webhook_url' => 'callbackUrl'
                ],
                'response_mapping' => [
                    'statusCode' => 'status',
                    'transactionRef' => 'reference',
                    'transactionId' => 'transaction_id'
                ],
                'transformation_rules' => [
                    [
                        'type' => 'format_date',
                        'field' => 'transactionDate',
                        'format' => 'Y-m-d H:i:s'
                    ],
                    [
                        'type' => 'uppercase',
                        'field' => 'mobile_network'
                    ]
                ]
            ],
            'COLLECTION_BALANCE' => [
                'name' => 'Test Bank to Tembo Collection Balance',
                'description' => 'Mapping for Test Bank to use Tembo Collection Balance Service',
                'request_mapping' => [],
                'response_mapping' => [
                    'availableBalance' => 'available_balance',
                    'currentBalance' => 'current_balance',
                    'accountNo' => 'account_number',
                    'accountStatus' => 'account_status',
                    'accountName' => 'account_name'
                ],
                'transformation_rules' => []
            ],
            'COLLECTION_STATEMENT' => [
                'name' => 'Test Bank to Tembo Collection Statement',
                'description' => 'Mapping for Test Bank to use Tembo Collection Statement Service',
                'request_mapping' => [
                    'start_date' => 'startDate',
                    'end_date' => 'endDate'
                ],
                'response_mapping' => [
                    'accountNo' => 'account_number',
                    'debitOrCredit' => 'transaction_type',
                    'tranRefNo' => 'transaction_reference',
                    'narration' => 'description',
                    'txnDate' => 'transaction_date',
                    'valueDate' => 'value_date',
                    'amountCredited' => 'amount_credited',
                    'amountDebited' => 'amount_debited',
                    'balance' => 'balance'
                ],
                'transformation_rules' => [
                    [
                        'type' => 'format_date',
                        'field' => 'startDate',
                        'format' => 'Y-m-d'
                    ],
                    [
                        'type' => 'format_date',
                        'field' => 'endDate',
                        'format' => 'Y-m-d'
                    ]
                ]
            ],
            'PAYMENT_STATUS' => [
                'name' => 'Test Bank to Tembo Payment Status',
                'description' => 'Mapping for Test Bank to use Tembo Payment Status Service',
                'request_mapping' => [
                    'transaction_id' => 'transactionId',
                    'reference' => 'transactionRef'
                ],
                'response_mapping' => [
                    'statusCode' => 'status',
                    'transactionId' => 'transaction_id',
                    'transactionRef' => 'reference'
                ],
                'transformation_rules' => []
            ]
        ];

        foreach ($services as $service) {
            $mapping = $mappings[$service->code] ?? null;
            if ($mapping) {
                ServiceMapping::updateOrCreate(
                    [
                        'client_id' => $client->id,
                        'aggregator_id' => $aggregator->id,
                        'service_id' => $service->id
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
                            'default_channel' => 'TZ-AIRTEL-C2B',
                            'default_narration' => 'Payment from Test Bank'
                        ]
                    ]
                );
            }
        }
    }
}
