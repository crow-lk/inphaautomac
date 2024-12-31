<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCustomersName extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add an index to the name column
            $table->index('name'); // Create an index on the name column
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop the index if rolling back
            $table->dropIndex(['name']);
        });
    }
}
