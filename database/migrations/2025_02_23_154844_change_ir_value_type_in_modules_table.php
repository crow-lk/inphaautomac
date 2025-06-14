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
        Schema::table('modules', function (Blueprint $table) {
            // Change the ir_value column to decimal
            $table->decimal('ir_value', 8, 3)->change()->nullable(); // 8 total digits, 2 decimal places
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            // Revert the ir_value column back to string if needed
            $table->string('ir_value')->change()->nullable();
        });
    }
};
