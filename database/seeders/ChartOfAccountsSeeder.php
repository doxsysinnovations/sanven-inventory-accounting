<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSeeder extends Seeder
{
    public function run()
    {
        DB::table('chart_of_accounts')->insert([
            [
                'code' => '1100',
                'name' => 'Accounts Receivable',
                'type' => 'asset',
                'category' => 'current asset',
                'normal_balance' => 'debit',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '1200',
                'name' => 'Cash',
                'type' => 'asset',
                'category' => 'current asset',
                'normal_balance' => 'debit',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '2000',
                'name' => 'Accounts Payable',
                'type' => 'liability',
                'category' => 'current liability',
                'normal_balance' => 'credit',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '2100',
                'name' => 'VAT Payable',
                'type' => 'liability',
                'category' => 'current liability',
                'normal_balance' => 'credit',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '4000',
                'name' => 'Sales Revenue',
                'type' => 'revenue',
                'category' => 'operating income',
                'normal_balance' => 'credit',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '5000',
                'name' => 'Cost of Goods Sold',
                'type' => 'expense',
                'category' => 'operating expense',
                'normal_balance' => 'debit',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
