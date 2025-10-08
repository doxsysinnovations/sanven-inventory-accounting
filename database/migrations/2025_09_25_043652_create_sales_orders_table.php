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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('order_date')->nullable();
            $table->date('requested_delivery_date')->nullable();
            $table->string('status')->default('draft');
            $table->string('payment_terms')->nullable();
            $table->string('payment_method')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->foreignId('agent_id')->nullable()->constrained('agents');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};