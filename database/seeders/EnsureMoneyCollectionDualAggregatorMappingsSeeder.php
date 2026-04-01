<?php

namespace Database\Seeders;

use App\Models\Aggregator;
use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceMapping;
use Illuminate\Database\Seeder;

/**
 * Ensures every client with a MONEY_COLLECTION mapping has both SELCOM (primary) and TEMBO (backup)
 * mappings when those aggregators and services exist in the database.
 */
class EnsureMoneyCollectionDualAggregatorMappingsSeeder extends Seeder
{
    public function run(): void
    {
        $selcom = Aggregator::where('code', 'SELCOM')->first();
        $tembo = Aggregator::where('code', 'TEMBO')->first();

        $selcomMoney = $selcom
            ? Service::where('code', 'MONEY_COLLECTION')->where('aggregator_id', $selcom->id)->first()
            : null;
        $temboMoney = $tembo
            ? Service::where('code', 'MONEY_COLLECTION')->where('aggregator_id', $tembo->id)->first()
            : null;

        if (!$selcom || !$tembo || !$selcomMoney || !$temboMoney) {
            $this->command->warn(
                'EnsureMoneyCollectionDualAggregatorMappingsSeeder: skipped (need SELCOM + TEMBO and each MONEY_COLLECTION service). Run ServicesSeeder then TemboAggregatorSeeder.'
            );

            return;
        }

        $clientIds = ServiceMapping::query()
            ->whereHas('service', fn ($q) => $q->where('code', 'MONEY_COLLECTION'))
            ->distinct()
            ->pluck('client_id');

        foreach ($clientIds as $clientId) {
            if (!Client::where('id', $clientId)->where('status', true)->exists()) {
                continue;
            }

            ServiceMapping::updateOrCreate(
                [
                    'client_id' => $clientId,
                    'aggregator_id' => $selcom->id,
                    'service_id' => $selcomMoney->id,
                ],
                [
                    'name' => 'Money Collection (Selcom — primary)',
                    'description' => 'Selcom push USSD / wallet collection; tried first for pay-by-link',
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
                    'status' => true,
                    'priority' => 0,
                    'settings' => ['aggregator_code' => 'SELCOM'],
                ]
            );

            ServiceMapping::updateOrCreate(
                [
                    'client_id' => $clientId,
                    'aggregator_id' => $tembo->id,
                    'service_id' => $temboMoney->id,
                ],
                [
                    'name' => 'Money Collection (Tembo — backup)',
                    'description' => 'Tembo collection; used if Selcom fails',
                    'request_mapping' => [
                        'customer_phone' => 'msisdn',
                        'mobile_network' => 'channel',
                        'amount' => 'amount',
                        'description' => 'narration',
                        'reference' => 'transactionRef',
                        'date' => 'transactionDate',
                        'webhook_url' => 'callbackUrl',
                    ],
                    'response_mapping' => [
                        'statusCode' => 'status',
                        'transactionRef' => 'reference',
                        'transactionId' => 'transaction_id',
                    ],
                    'transformation_rules' => [
                        ['type' => 'format_date', 'field' => 'transactionDate', 'format' => 'Y-m-d H:i:s'],
                        ['type' => 'uppercase', 'field' => 'mobile_network'],
                    ],
                    'status' => true,
                    'priority' => 1,
                    'settings' => ['aggregator_code' => 'TEMBO'],
                ]
            );
        }

        $this->command->info('Dual SELCOM + TEMBO MONEY_COLLECTION mappings ensured for clients with existing collection mappings.');
    }
}
