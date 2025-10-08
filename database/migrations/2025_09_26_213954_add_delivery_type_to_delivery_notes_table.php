<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->enum('delivery_type', [
                'SO_LINKED',
                'FREE_SAMPLE',
                'REPLACEMENT',
                'TRANSFER',
                'DONATION',
                'EMERGENCY_SUPPLY',
            ])->default('SO_LINKED')->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropColumn('delivery_type');
        });
    }
};
