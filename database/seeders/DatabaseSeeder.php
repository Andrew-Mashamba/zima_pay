<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call other seeders in the correct order
        $this->call([
            UsersSeeder::class,
            AggregatorsSeeder::class,
            ServicesSeeder::class,
            ClientsSeeder::class,
            TemboAggregatorSeeder::class,
            ServiceMappingsSeeder::class,
            EnsureMoneyCollectionDualAggregatorMappingsSeeder::class,
            PaymentLinksSeeder::class,
            PaymentLinkItemsSeeder::class,
            ClientAggregatorSeeder::class,
            ClientServiceSeeder::class,
        ]);
    }
}
