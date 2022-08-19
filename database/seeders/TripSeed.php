<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripsSeat;
use App\Models\TripsStation;
use Illuminate\Database\Seeder;

class TripSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\City::factory()->create(['name' => 'Cairo']);
        \App\Models\City::factory()->create(['name' => 'Giza']);
        \App\Models\City::factory()->create(['name' => 'AlFayyum']);
        \App\Models\City::factory()->create(['name' => 'AlMinya']);
        \App\Models\City::factory()->create(['name' => 'Asyut']);

        $bus = Bus::factory()->create([
            'name' => 'Cairo Bus',
        ]);

        $trip = Trip::factory()->create([
            'name' => 'Cairo Asyut Trip',
            'bus_id' => $bus->id
        ]);

        $cities = City::all();

        foreach ($cities as $key => $city){
            TripsStation::factory()->create([
                'trip_id' => $trip->id,
                'city_id' => $city->id,
                'station_order' => $key
            ]);
        }

        TripsSeat::factory($bus->seats_capacity)->create([
            'trip_id' => $trip->id
        ]);

    }
}
