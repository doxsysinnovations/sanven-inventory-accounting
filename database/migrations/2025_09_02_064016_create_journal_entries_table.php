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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('journal_no')->unique(); // e.g. JE-2025-0001
            $table->date('journal_date');
            $table->string('reference')->nullable(); // e.g. Invoice #, PO #
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of related model
            $table->string('reference_type')->nullable(); // e.g. Invoice, Payment, PO
            $table->string('description')->nullable();
            $table->enum('status', ['draft', 'posted', 'void'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
