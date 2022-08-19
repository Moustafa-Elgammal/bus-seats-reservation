<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips_stations', function (Blueprint $table) {
            $table->id();
            $table->integer('trip_id');
            $table->integer('city_id');
            $table->smallInteger('station_order');
            $table->timestamps();
            $table->index(['trip_id', 'city_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trips_stations');
    }
};
