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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable(); // linked invoice
            $table->decimal('amount_paid', 15, 2); // payment amount
            $table->date('payment_date'); // payment date
            $table->string('payment_method'); // cash, bank_transfer, gcash, etc.
            $table->string('reference')->nullable(); // OR#, transaction ID, etc.
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'collected', 'reversed', 'failed', 'refunded'])
                ->default('collected');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();

            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
