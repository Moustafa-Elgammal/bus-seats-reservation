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
            $table->foreignId('trip_id')->references('id')->on('trips')->cascadeOnDelete();
            $table->foreignId('city_id')->references('id')->on('cities')->restrictOnDelete();
            $table->smallInteger('station_order');
            $table->timestamps();
            // a city appears at most once on a route, and every stop holds a
            // distinct position — both are relied on by TripService's index maths
            $table->unique(['trip_id', 'city_id']);
            $table->unique(['trip_id', 'station_order']);
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
