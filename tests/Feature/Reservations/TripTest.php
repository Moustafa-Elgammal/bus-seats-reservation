<?php

namespace Tests\Feature\Reservations;

use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripsSeat;
use App\Models\TripsStation;
use App\Services\Seats\TripSeatService;
use App\Services\Trips\TripService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TripTest extends TestCase
{
    use RefreshDatabase;


    public function test_get_trip_stations_orders(){
        // seed init cities
        $from = \App\Models\City::factory()->create(['name' => 'Cairo']);
        $in = \App\Models\City::factory()->create(['name' => 'AlMinya']);
        $to = \App\Models\City::factory()->create(['name' => 'Asyut']);

        // create bus
        $bus = Bus::factory()->create([
            'name' => 'Cairo Bus',
        ]);

        // attach a trip to bus
        $trip = Trip::factory()->create([
            'name' => 'Cairo Asyut Trip',
            'bus_id' => $bus->id
        ]);

        // get some or all cities to create the trip route
        $cities = City::all();
        foreach ($cities as $key => $city){
            TripsStation::factory()->create([
                'trip_id' => $trip->id,
                'city_id' => $city->id,
                'station_order' => $key
            ]);
        }

        // generate the trip seats
        TripsSeat::factory($bus->seats_capacity)->create([
            'trip_id' => $trip->id
        ]);

        $tripService = new TripService();
        $this->assertEquals($tripService->getTripStationsOrders($trip->id), [$from->id, $in->id, $to->id]);
    }

    public function test_get_needed_stops_from_trip(){
        // seed init cities
        $from = \App\Models\City::factory()->create(['name' => 'Cairo']);
        $to = \App\Models\City::factory()->create(['name' => 'Giza']);
        \App\Models\City::factory()->create(['name' => 'AlFayyum']);
        \App\Models\City::factory()->create(['name' => 'AlMinya']);
        \App\Models\City::factory()->create(['name' => 'Asyut']);

        // create bus
        $bus = Bus::factory()->create([
            'name' => 'Cairo Bus',
        ]);

        // attach a trip to bus
        $trip = Trip::factory()->create([
            'name' => 'Cairo Asyut Trip',
            'bus_id' => $bus->id
        ]);

        // get some or all cities to create the trip route
        $cities = City::all();
        foreach ($cities as $key => $city){
            TripsStation::factory()->create([
                'trip_id' => $trip->id,
                'city_id' => $city->id,
                'station_order' => $key
            ]);
        }

        // generate the trip seats
        TripsSeat::factory($bus->seats_capacity)->create([
            'trip_id' => $trip->id
        ]);

        $tripService = new TripService();
        $this->assertEquals($tripService->getNeededStopsFromTrip($trip->id,$from->id,$to->id), [$from->id]);
        $this->assertEquals($tripService->getNeededStopsFromTrip($trip->id,$from->id,$to->id +1), [$from->id, $to->id]);

    }

    public function test_validate_route_trip(){
        // seed init cities
        $from = \App\Models\City::factory()->create(['name' => 'Cairo']);
        $to = \App\Models\City::factory()->create(['name' => 'Giza']);
        \App\Models\City::factory()->create(['name' => 'AlFayyum']);
        \App\Models\City::factory()->create(['name' => 'AlMinya']);
        \App\Models\City::factory()->create(['name' => 'Asyut']);

        // create bus
        $bus = Bus::factory()->create([
            'name' => 'Cairo Bus',
        ]);

        // attach a trip to bus
        $trip = Trip::factory()->create([
            'name' => 'Cairo Asyut Trip',
            'bus_id' => $bus->id
        ]);

        // get some or all cities to create the trip route
        $cities = City::all();
        foreach ($cities as $key => $city){
            TripsStation::factory()->create([
                'trip_id' => $trip->id,
                'city_id' => $city->id,
                'station_order' => $key
            ]);
        }

        // generate the trip seats
        TripsSeat::factory($bus->seats_capacity)->create([
            'trip_id' => $trip->id
        ]);

        $tripService = new TripService();

        // check same value from
        $this->assertFalse($tripService->validateNeededTripRoute($trip->id,$from->id, $from->id));

        // check same value to
        $this->assertFalse($tripService->validateNeededTripRoute($trip->id,$to->id, $to->id));

        // cities not included
        $this->assertFalse($tripService->validateNeededTripRoute($trip->id, $from->id + 100, $to->id + 100));

        // reversed route
        $this->assertFalse($tripService->validateNeededTripRoute($trip->id,$to->id, $from->id));

        // normal case
        $this->assertTrue($tripService->validateNeededTripRoute($trip->id,$from->id,$to->id +1));
    }

}
