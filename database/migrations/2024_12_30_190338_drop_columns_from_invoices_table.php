<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnsFromInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the columns you want to remove
            $table->dropColumn(['customer_id', 'vehicle_id']); // Replace with your actual column names
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Optionally, you can add the columns back in the down method
            $table->string('customer_id'); // Replace with the original column definition
            $table->string('vehicle_id'); // Replace with the original column definition
        });
    }
}
