<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_note_item_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_note_item_id')
                  ->constrained('delivery_note_items')
                  ->onDelete('cascade');
            $table->foreignId('stock_id')
                  ->constrained('stocks')
                  ->onDelete('restrict');
            $table->integer('allocated_qty')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_note_item_batches');
    }
};
