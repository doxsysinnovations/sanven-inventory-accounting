<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('pending','paid','partially_paid','overdue','cancelled') DEFAULT 'pending'");
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('pending','paid','overdue','cancelled') DEFAULT 'pending'");
    }
};
