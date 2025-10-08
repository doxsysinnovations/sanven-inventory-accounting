<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_create_chart_of_accounts_table.php
    public function up()
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., 1000, 2000
            $table->string('name');           // e.g., Cash, Accounts Receivable

            // Main account type
            $table->enum('type', [
                'asset',
                'liability',
                'equity',
                'revenue',
                'expense'
            ]);

            // Sub-classification (current vs fixed, operating vs non-operating, etc.)
            $table->string('category')->nullable();
            // Example: 'current asset', 'fixed asset', 'current liability', 'operating expense'

            // Debit or Credit (normal balance of account)
            $table->enum('normal_balance', ['debit', 'credit'])->nullable();

            // Opening balance (for when books start)
            $table->decimal('opening_balance', 15, 2)->default(0);

            // Status (active/inactive)
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
