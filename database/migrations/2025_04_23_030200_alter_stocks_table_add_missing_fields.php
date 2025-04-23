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
        Schema::table('stocks', function (Blueprint $table) {
            // Add missing fields
            $table->string('product_name')->nullable()->after('product_id');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('cascade')->after('quantity');
            $table->date('manufactured_date')->nullable()->after('expiration_date');
            $table->string('stock_location')->nullable()->after('location');
            $table->string('invoice_number')->nullable()->after('stock_location');
            $table->text('batch_notes')->nullable()->after('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Drop the added fields
            $table->dropColumn('product_name');
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
            $table->dropColumn('manufactured_date');
            $table->dropColumn('stock_location');
            $table->dropColumn('invoice_number');
            $table->dropColumn('batch_notes');
        });
    }
};