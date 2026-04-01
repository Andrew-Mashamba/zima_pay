<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentLinkItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentLinkItems = [
            [
                'id' => 1,
                'payment_link_id' => 2,
                'item_code' => 'ITEM_jr3EEGkbb7oZ',
                'item_name' => 'Loan Repayment - Installment #3 of 12',
                'item_description' => 'Monthly loan installment payment',
                'amount' => 75000.00,
                'paid_amount' => 0.00,
                'currency' => 'TZS',
                'is_required' => true,
                'allow_partial' => false,
                'minimum_amount' => 75000.00,
                'quantity' => 1,
                'unit' => null,
                'metadata' => '{"product_service_reference":"LOAN_2025_001","security_hash":"962da1f90cbc1930060cbcd7caf3aba90611a4e8528234f69c2d5a646b2e1d22"}',
                'category' => 'service',
                'subcategory' => null,
                'status' => 'pending',
                'paid_at' => null,
                'created_at' => '2025-07-20 11:56:57',
                'updated_at' => '2025-07-20 11:56:57',
            ],
            [
                'id' => 2,
                'payment_link_id' => 3,
                'item_code' => 'ITEM_PSvybwCpGzXW',
                'item_name' => 'Business Registration Fee',
                'item_description' => 'Annual business registration renewal',
                'amount' => 150000.00,
                'paid_amount' => 0.00,
                'currency' => 'TZS',
                'is_required' => true,
                'allow_partial' => false,
                'minimum_amount' => 150000.00,
                'quantity' => 1,
                'unit' => null,
                'metadata' => '{"product_service_reference":"BUS_REG_2025_002","security_hash":"850073c3c36e4923b8175a7a770e308968176b3f48911d11b46fbcb8139569ae"}',
                'category' => 'service',
                'subcategory' => null,
                'status' => 'pending',
                'paid_at' => null,
                'created_at' => '2025-07-20 11:56:57',
                'updated_at' => '2025-07-20 11:56:57',
            ],
            [
                'id' => 3,
                'payment_link_id' => 4,
                'item_code' => 'ITEM_rub4JwFHEFj6',
                'item_name' => 'Insurance Premium Payment',
                'item_description' => 'Quarterly health insurance premium',
                'amount' => 45000.00,
                'paid_amount' => 0.00,
                'currency' => 'TZS',
                'is_required' => true,
                'allow_partial' => false,
                'minimum_amount' => 45000.00,
                'quantity' => 1,
                'unit' => null,
                'metadata' => '{"product_service_reference":"INS_2025_003","security_hash":"cbca4fd4a37e059292ed3e033fb745956bde3a7766d811510dc6f93c06befdcc"}',
                'category' => 'service',
                'subcategory' => null,
                'status' => 'pending',
                'paid_at' => null,
                'created_at' => '2025-07-20 11:56:57',
                'updated_at' => '2025-07-20 11:56:57',
            ],
            [
                'id' => 4,
                'payment_link_id' => 5,
                'item_code' => 'ITEM_HtZcoKaJd1hf',
                'item_name' => 'General Donation',
                'item_description' => 'General church donation',
                'amount' => 10000.00,
                'paid_amount' => 0.00,
                'currency' => 'TZS',
                'is_required' => true,
                'allow_partial' => true,
                'minimum_amount' => 5000.00,
                'quantity' => 1,
                'unit' => null,
                'metadata' => '{"product_service_reference":"CHURCH_SADAKA_001_9ITG","security_hash":"42ba60ff08529e980a3670db324a4fae13abb564c681222f8e8b46650091f317"}',
                'category' => 'service',
                'subcategory' => null,
                'status' => 'pending',
                'paid_at' => null,
                'created_at' => '2025-07-20 11:56:57',
                'updated_at' => '2025-07-20 11:56:57',
            ],
            [
                'id' => 5,
                'payment_link_id' => 5,
                'item_code' => 'ITEM_HRYOyzFT1U6Y',
                'item_name' => 'Building Fund',
                'item_description' => 'Church building fund contribution',
                'amount' => 5000.00,
                'paid_amount' => 0.00,
                'currency' => 'TZS',
                'is_required' => true,
                'allow_partial' => true,
                'minimum_amount' => 2000.00,
                'quantity' => 1,
                'unit' => null,
                'metadata' => '{"product_service_reference":"CHURCH_SADAKA_001_LQLT","security_hash":"d7fa5599f369a47ec53840fb11e12d401ca2efcc8deaf37dbddf8e786a453119"}',
                'category' => 'service',
                'subcategory' => null,
                'status' => 'pending',
                'paid_at' => null,
                'created_at' => '2025-07-20 11:56:57',
                'updated_at' => '2025-07-20 11:56:57',
            ],
            [
                'id' => 6,
                'payment_link_id' => 6,
                'item_code' => 'ITEM_r5wMEmzabJuZ',
                'item_name' => 'Education Fund',
                'item_description' => 'Support for local school projects',
                'amount' => 15000.00,
                'paid_amount' => 0.00,
                'currency' => 'TZS',
                'is_required' => true,
                'allow_partial' => true,
                'minimum_amount' => 5000.00,
                'quantity' => 1,
                'unit' => null,
                'metadata' => '{"product_service_reference":"COMMUNITY_DEV_001_E7VU","security_hash":"679a0ca61f0328ce69df0977e6a52761e68102723f3431f3318a1bd3536dd4f3"}',
                'category' => 'service',
                'subcategory' => null,
                'status' => 'pending',
                'paid_at' => null,
                'created_at' => '2025-07-20 11:56:57',
                'updated_at' => '2025-07-20 11:56:57',
            ],
            [
                'id' => 7,
                'payment_link_id' => 6,
                'item_code' => 'ITEM_yqudDwmT40Ui',
                'item_name' => 'Healthcare Fund',
                'item_description' => 'Support for local clinic improvements',
                'amount' => 10000.00,
                'paid_amount' => 0.00,
                'currency' => 'TZS',
                'is_required' => true,
                'allow_partial' => true,
                'minimum_amount' => 5000.00,
                'quantity' => 1,
                'unit' => null,
                'metadata' => '{"product_service_reference":"COMMUNITY_DEV_001_KUMA","security_hash":"b8c0ebe73613f2f81df28c3deaabba97171c3955a02715f52ba94c3d8294a0b3"}',
                'category' => 'service',
                'subcategory' => null,
                'status' => 'pending',
                'paid_at' => null,
                'created_at' => '2025-07-20 11:56:57',
                'updated_at' => '2025-07-20 11:56:57',
            ],
        ];

        // Clear existing data (disable FK checks so truncate works when other tables reference payment_link_items)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('payment_link_items')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE payment_link_items CASCADE');
        } else {
            DB::table('payment_link_items')->truncate();
        }

        // Insert new data
        foreach ($paymentLinkItems as $item) {
            DB::table('payment_link_items')->insert($item);
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval('payment_link_items_id_seq', (SELECT MAX(id) FROM payment_link_items))");
        }

        $this->command->info('Payment Link Items seeded successfully!');
    }
}
