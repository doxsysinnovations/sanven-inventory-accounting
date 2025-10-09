<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();

            // Foreign key to journal_entries table
            $table->foreignId('journal_entry_id')
                ->constrained('journal_entries')
                ->onDelete('cascade');

            // Foreign key to chart_of_accounts table (was incorrectly 'accounts')
            $table->foreignId('account_id')
                ->constrained('chart_of_accounts')
                ->onDelete('cascade');

            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->text('memo')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};
