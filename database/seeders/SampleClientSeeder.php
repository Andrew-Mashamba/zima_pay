<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceMapping;

class SampleClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample client
        $client = Client::firstOrCreate(
            ['code' => 'SAMPLE_PAY'],
            [
                'name' => 'Sample Payment Gateway',
                'code' => 'SAMPLE_PAY',
                'api_key' => 'sample_client_key_ABC123DEF456',
                'api_secret' => 'sample_client_secret_XYZ789GHI012',
                'webhook_url' => 'https://webhook.site/sample-client-webhook',
                'status' => true,
                'contact_person' => 'John Doe',
                'contact_email' => 'john.doe@samplepay.com',
                'contact_phone' => '+255789123456',
                'description' => 'Sample payment gateway for testing ZIMA ESB integration',
                'settings' => [
                    'max_transaction_amount' => 1000000,
                    'min_transaction_amount' => 100,
                    'allowed_networks' => ['TZ-AIRTEL-C2B', 'TZ-TIGO-C2B', 'TZ-MPESA-C2B', 'TZ-HALOPESA-C2B'],
                    'webhook_retry_attempts' => 3,
                    'webhook_timeout' => 30,
                    'auto_reconciliation' => true,
                    'risk_assessment_enabled' => true
                ]
            ]
        );

        // Get the money collection service
        $service = Service::where('code', 'MONEY_COLLECTION')->first();

        if ($service) {
            // Create service mapping for the sample client
            ServiceMapping::firstOrCreate(
                [
                    'client_id' => $client->id,
                    'service_id' => $service->id,
                    'aggregator_id' => 1
                ],
                [
                    'name' => 'Sample Payment Gateway - Money Collection',
                    'description' => 'Service mapping for Sample Payment Gateway money collection',
                    'client_id' => $client->id,
                    'service_id' => $service->id,
                    'aggregator_id' => 1, // TEMBO aggregator
                    'status' => true,
                    'request_mapping' => [
                        'customer_phone' => 'customer_phone',
                        'mobile_network' => 'mobile_network',
                        'amount' => 'amount',
                        'description' => 'description',
                        'reference' => 'reference',
                        'date' => 'date',
                        'webhook_url' => 'webhook_url'
                    ],
                    'response_mapping' => [
                        'transaction_id' => 'transaction_id',
                        'reference' => 'reference',
                        'status' => 'status',
                        'message' => 'message'
                    ],
                    'settings' => [
                        'commission_rate' => 2.5,
                        'transaction_fee' => 50,
                        'daily_limit' => 10000000,
                        'monthly_limit' => 100000000,
                        'allowed_amount_ranges' => [
                            'small' => ['min' => 100, 'max' => 1000],
                            'medium' => ['min' => 1001, 'max' => 50000],
                            'large' => ['min' => 50001, 'max' => 1000000]
                        ],
                        'webhook_events' => ['transaction.created', 'transaction.completed', 'transaction.failed'],
                        'retry_config' => [
                            'max_attempts' => 3,
                            'delay_seconds' => [5, 15, 60]
                        ]
                    ]
                ]
            );

            $this->command->info("✅ Sample client 'Sample Payment Gateway' created successfully!");
            $this->command->info("📋 Client Details:");
            $this->command->info("   - Name: {$client->name}");
            $this->command->info("   - Code: {$client->code}");
            $this->command->info("   - API Key: {$client->api_key}");
            $this->command->info("   - API Secret: {$client->api_secret}");
            $this->command->info("   - Webhook URL: {$client->webhook_url}");
            $this->command->info("   - Status: " . ($client->status ? 'Active' : 'Inactive'));
            $this->command->info("   - Contact: {$client->contact_person} ({$client->contact_email})");
            $this->command->info("");
            $this->command->info("🔗 Service Mapping:");
            $this->command->info("   - Service: {$service->name} ({$service->code})");
            $this->command->info("   - Aggregator: TEMBO");
            $this->command->info("   - Status: Active");
            $this->command->info("");
            $this->command->info("🎯 Ready for testing! Use the API credentials above to make requests.");
        } else {
            $this->command->error("❌ Money collection service not found!");
        }
    }
} 