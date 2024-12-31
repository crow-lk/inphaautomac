<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerNameToVehiclesTable extends Migration
{
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {

            // Create a foreign key constraint (not recommended for non-ID columns)
            $table->foreign('customer_name')->references('name')->on('customers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop the foreign key constraint and the column
            $table->dropForeign(['customer_name']);
        });
    }
}
