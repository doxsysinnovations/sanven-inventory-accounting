<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'approved',
                'partially delivered',
                'delivered',
                'closed',
                'cancelled'
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'partially delivered',
                'delivered',
                'closed',
                'cancelled'
            ])->default('pending')->change();
        });
    }
};