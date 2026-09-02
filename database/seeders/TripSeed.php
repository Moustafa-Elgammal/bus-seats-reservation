<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripStation;
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
        // seed init cities
        City::factory()->create(['name' => 'Cairo']);
        City::factory()->create(['name' => 'Giza']);
        City::factory()->create(['name' => 'AlFayyum']);
        City::factory()->create(['name' => 'AlMinya']);
        City::factory()->create(['name' => 'Asyut']);

        // create bus
        $bus = Bus::factory()->create([
            'name' => 'Cairo Bus',
        ]);

        // attach a trip to bus
        $trip = Trip::factory()->create([
            'name' => 'Cairo Asyut Trip',
            'bus_id' => $bus->id,
        ]);

        // get some or all cities to create the trip route
        $cities = City::all();
        foreach ($cities as $key => $city) {
            TripStation::factory()->create([
                'trip_id' => $trip->id,
                'city_id' => $city->id,
                'station_order' => $key,
            ]);
        }

    }
}
