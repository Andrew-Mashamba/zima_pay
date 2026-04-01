<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientServices = [
            [
                'id' => 1,
                'client_id' => 2,
                'service_id' => 1,
                'status' => 1,
                'rate_limit' => 50,
                'quota' => 1000,
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 15:27:01',
            ],
            [
                'id' => 2,
                'client_id' => 2,
                'service_id' => 2,
                'status' => 1,
                'rate_limit' => 50,
                'quota' => 1000,
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 15:27:01',
            ],
            [
                'id' => 3,
                'client_id' => 2,
                'service_id' => 3,
                'status' => 1,
                'rate_limit' => 50,
                'quota' => 1000,
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 15:27:01',
            ],
            [
                'id' => 4,
                'client_id' => 2,
                'service_id' => 4,
                'status' => 1,
                'rate_limit' => 50,
                'quota' => 1000,
                'created_at' => '2025-07-20 11:49:12',
                'updated_at' => '2025-07-20 15:27:01',
            ],
        ];

        // Clear existing data (disable FK checks so truncate works when other tables reference client_service)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('client_service')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE client_service CASCADE');
        } else {
            DB::table('client_service')->truncate();
        }

        // Insert new data
        foreach ($clientServices as $clientService) {
            DB::table('client_service')->insert($clientService);
        }

        $this->command->info('Client Service relationships seeded successfully!');
    }
}
