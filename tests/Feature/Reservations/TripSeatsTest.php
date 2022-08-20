<?php

namespace Tests\Feature\Reservations;

use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripsSeat;
use App\Models\TripsStation;
use App\Models\User;
use App\Services\Seats\TripSeatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripSeatsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_trip_seats()
    {
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
        foreach ($cities as $key => $city) {
            TripsStation::factory()->create([
                'trip_id' => $trip->id,
                'city_id' => $city->id,
                'station_order' => $key
            ]);
        }

        // generate the trip seats
        $seats = TripsSeat::factory($bus->seats_capacity)->create([
            'trip_id' => $trip->id
        ]);

        // check colums
        $needed = ['id', 'trip_id', 'city_id', 'station_order'];
        $this->assertEquals(TripSeatService::getTripSeats($trip->id)->pluck($needed), $seats->pluck($needed));
    }

    public function test_check_seat_reservation()
    {
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
        foreach ($cities as $key => $city) {
            TripsStation::factory()->create([
                'trip_id' => $trip->id,
                'city_id' => $city->id,
                'station_order' => $key
            ]);
        }

        // generate the trip seats
        $seats = TripsSeat::factory($bus->seats_capacity)->create([
            'trip_id' => $trip->id
        ]);

        $user = User::factory()->create();
        $check = (new \App\Services\Reservations\ReservationService(new \App\Services\Trips\TripService()))
            ->bookSeat($trip->id, $seats[2]->id, $from->id, $to->id, $user->id);

        $this->assertTrue($check);

        // reserved seat
        $this->assertFalse(TripSeatService::checkSeatReservations($seats[2]->id, [$from->id, $in->id]));

        // unreserved seat
        $this->assertTrue(TripSeatService::checkSeatReservations($seats[1]->id, [$from->id, $in->id]));
    }

    public function test_check_seat_belong_toTrip()
    {
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
        foreach ($cities as $key => $city) {
            TripsStation::factory()->create([
                'trip_id' => $trip->id,
                'city_id' => $city->id,
                'station_order' => $key
            ]);
        }

        // generate the trip seats
        $seats = TripsSeat::factory($bus->seats_capacity)->create([
            'trip_id' => $trip->id
        ]);

        $this->assertTrue(TripSeatService::checkSeatBelognToTrip($seats[1]->id, $trip->id));
        $this->assertFalse(TripSeatService::checkSeatBelognToTrip($seats[1]->id, $trip->id - 1));


    }
}
