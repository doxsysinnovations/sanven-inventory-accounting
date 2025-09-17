<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JournalEntry;

class JournalEntriesSeeder extends Seeder
{
    public function run()
    {
        $entry = JournalEntry::create([
            'journal_no' => 'JE-2025-0001',
            'journal_date' => now(),
            'reference_type' => 'App\Models\Invoice',
            'reference_id' => 1,
            'description' => 'Invoice #INV-1001 for Customer Juan Dela Cruz',
            'status' => 'posted',
        ]);

        // Debit Accounts Receivable
        $entry->lines()->create([
            'account_id' => 1100,
            'debit' => 56000,
            'credit' => 0,
            'memo' => 'Accounts Receivable for Invoice #INV-1001',
        ]);

        // Credit Sales Revenue
        $entry->lines()->create([
            'account_id' => 4000,
            'debit' => 0,
            'credit' => 50000,
            'memo' => 'Sales Revenue for Invoice #INV-1001',
        ]);

        // Credit VAT Payable
        $entry->lines()->create([
            'account_id' => 2100,
            'debit' => 0,
            'credit' => 6000,
            'memo' => 'VAT Payable for Invoice #INV-1001',
        ]);
    }
}
