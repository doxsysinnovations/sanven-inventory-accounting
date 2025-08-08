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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('purchaser_id')->constrained('users');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->enum('order_type', ['stock', 'others'])->default('stock');
            $table->enum('status', ['pending', 'partially delivered','delivered', 'closed', 'cancelled'])->default('pending');
            $table->text('remarks')->nullable();
            $table->enum('payment_terms', ['net 15', 'net 30', 'net 60', '50% downpayment', 'installments', 'upon delivery'])->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
