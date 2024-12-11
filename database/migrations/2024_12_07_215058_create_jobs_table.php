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
        Schema::create('inpha_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_id');
            //relationship to customer
            $table->foreignId('customer_id')->constrained();

            //relationship to vehicle
            $table->foreignId('vehicle_id')->constrained();

            //relationship to services
            $table->json('service_ids');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
};
