<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientAggregatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientAggregators = [
            [
                'id' => 1,
                'client_id' => 2,
                'aggregator_id' => 1,
                'status' => 1,
                'api_credentials' => '{"account_id":"bf71ba501b37d989db6224fd","secret_key":"vd//lqSw67Nl08e7Y2YzWcs2EL+rAIImpl4U4uNHdQg="}',
                'rate_limit' => 50,
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 15:27:01',
            ],
        ];

        // Clear existing data
        DB::table('client_aggregator')->truncate();

        // Insert new data
        foreach ($clientAggregators as $clientAggregator) {
            DB::table('client_aggregator')->insert($clientAggregator);
        }

        $this->command->info('Client Aggregator relationships seeded successfully!');
    }
}
