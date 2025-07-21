<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AggregatorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aggregators = [
            [
                'id' => 1,
                'name' => 'Tembo Plus',
                'code' => 'TEMBO',
                'description' => 'Tembo Plus Mobile Money Collection Service for Tanzania',
                'api_endpoint' => 'https://sandbox.temboplus.com/tembo/v1',
                'api_key' => 'bf71ba501b37d989db6224fd',
                'api_secret' => 'vd//lqSw67Nl08e7Y2YzWcs2EL+rAIImpl4U4uNHdQg=',
                'webhook_url' => null,
                'status' => 1,
                'rate_limit' => 1000,
                'timeout' => 30,
                'retry_attempts' => 3,
                'contact_person' => 'Tembo Support',
                'contact_email' => 'support@temboplus.com',
                'contact_phone' => null,
                'settings' => '{"headers":{"content-type":"application\/json"},"supported_channels":["TZ-TIGO-C2B","TZ-AIRTEL-C2B"]}',
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 11:49:12',
            ],
        ];

        // Clear existing data
        DB::table('aggregators')->truncate();

        // Insert new data
        foreach ($aggregators as $aggregator) {
            DB::table('aggregators')->insert($aggregator);
        }

        // Reset PostgreSQL sequence for aggregators table
        DB::statement("SELECT setval('aggregators_id_seq', (SELECT MAX(id) FROM aggregators))");

        $this->command->info('Aggregators seeded successfully!');
    }
}
