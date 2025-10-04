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
        Schema::create('database_backup_histories', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('disk');
            $table->decimal('file_size', 10, 2)->nullable(); // in MB
            $table->string('status')->default('success'); // success, failed
            $table->text('error_message')->nullable();
            $table->timestamp('backup_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_backup_histories');
    }
};
