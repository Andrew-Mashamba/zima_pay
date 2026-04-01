<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'name' => 'Andrew Mashamba',
                'email' => 'andrew.s.mashamba@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$ajSBHV2T0TUcNlcPpUHQReph6aQIz.1ZFJk4xa7Ec5LKpofV/Rkeq',
                'remember_token' => null,
                'current_team_id' => null,
                'profile_photo_path' => null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'role' => 'user',
                'is_active' => 1,
                'phone' => null,
                'department' => null,
                'position' => null,
                'location' => null,
                'notes' => null,
                'last_login_at' => null,
                'last_login_ip' => null,
                'created_at' => '2025-07-20 11:41:31',
                'updated_at' => '2025-07-20 11:41:31',
            ],
        ];

        // Clear existing data (disable FK checks so truncate works when other tables reference users)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('users')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE users CASCADE');
        } else {
            DB::table('users')->truncate();
        }

        // Insert new data
        foreach ($users as $user) {
            DB::table('users')->insert($user);
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval('users_id_seq', (SELECT MAX(id) FROM users))");
        }

        $this->command->info('Users seeded successfully!');
    }
}
