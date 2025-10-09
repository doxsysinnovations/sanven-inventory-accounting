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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('stock_number')->unique();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('cascade');            $table->integer('quantity')->default(1);
            $table->decimal('capital_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('location')->nullable();
            $table->string('barcode')->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('is_initial')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
