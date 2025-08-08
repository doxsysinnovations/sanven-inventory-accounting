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
        Schema::create('payables', function (Blueprint $table) {
            $table->id();
            $table->string('payable_no')->unique(); // PAY-2025-0001 (generated)
            
            // Source/Type
            $table->string('type'); // e.g., Agent Commission, Supplier Invoice
            $table->string('reference_no')->nullable(); // e.g., COM-00012, INV-00045

            // Payee Info
            $table->string('payee_name');
            $table->unsignedBigInteger('payee_id')->nullable(); // link to agents/suppliers if needed

            // Financials
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');

            // Payment Details
            $table->enum('payment_method', ['bank_transfer', 'cash', 'check', 'ewallet', 'other'])->nullable();
            $table->date('payment_date')->nullable();
            $table->string('attachment')->nullable(); // store file path or URL
            $table->text('remarks')->nullable();

            // Audit Trail
            $table->string('created_by')->nullable(); // or foreign key to users table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payables');
    }
};
