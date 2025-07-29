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
        if (!Schema::hasColumn('quotations', 'discount_type')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->enum('discount_type', ['fixed', 'percentage'])->default('fixed')->after('discount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('quotations', 'discount_type')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropColumn('discount_type');
            });
        }
    }
};