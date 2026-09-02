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
        Schema::create('reservations_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->references('id')->on('customers_seats_reservations')->cascadeOnDelete();
            $table->foreignId('city_id')->references('id')->on('cities')->restrictOnDelete();
            $table->timestamps();
            $table->index('reservation_id');
            $table->index('city_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservations_stops');
    }
};
