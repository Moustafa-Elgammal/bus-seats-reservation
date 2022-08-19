<?php

namespace Tests\Feature\Reservations;

use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripsSeat;
use App\Models\TripsStation;
use App\Services\Reservations\ReservationService;
use App\Services\Trips\TripService;
use Tests\TestCase;

class ReservationsTest extends TestCase
{
    protected $reservationService;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->reservationService = new ReservationService(new TripService());

        parent::__construct($name, $data, $dataName);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_available_seats_of_trip()
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
        foreach ($cities as $key => $city){
            TripsStation::factory()->create([
                'trip_id' => $trip->id,
                'city_id' => $city->id,
                'station_order' => $key
            ]);
        }

        // generate the trip seats
        $seets = TripsSeat::factory($bus->seats_capacity)->create([
            'trip_id' => $trip->id
        ]);

        $this->assertEquals(
            count($this->reservationService->getAvailableSeatsOfTrip($trip->id, $from->id, $to->id)),
            $bus->seats_capacity);


        // do reserve
        $this->reservationService->bookSeat($trip->id, $seets[2]->id, $from->id, $to->id, 1);

        $this->assertEquals(
            count($this->reservationService->getAvailableSeatsOfTrip($trip->id, $from->id, $to->id)),
            $bus->seats_capacity - 1);

        // do reserve
        $this->reservationService->bookSeat($trip->id, $seets[2]->id, $from->id, $in->id, 1);

        $this->assertEquals(
            count($this->reservationService->getAvailableSeatsOfTrip($trip->id, $in->id, $to->id)),
            $bus->seats_capacity - 1);

    }


    public function test_book_set()
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
        foreach ($cities as $key => $city){
            TripsStation::factory()->create([
                'trip_id' => $trip->id,
                'city_id' => $city->id,
                'station_order' => $key
            ]);
        }

        // generate the trip seats
        $seets = TripsSeat::factory($bus->seats_capacity)->create([
            'trip_id' => $trip->id
        ]);

        // get seats
        //

        $this->assertEquals(
            count($this->reservationService->getAvailableSeatsOfTrip($trip->id, $from->id, $to->id)),
            $bus->seats_capacity);


        // first reservation of seat
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seets[2]->id, $from->id, $to->id, 1));

        // check second attempt of the same reservation
        $this->assertFalse($this->reservationService->bookSeat($trip->id, $seets[2]->id, $from->id, $to->id, 1));


        // reserve the half route
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seets[1]->id, $from->id, $in->id, 1));
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seets[1]->id, $in->id, $to->id, 1));


        // reserve last two station then first two station

        // reserve the half route
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seets[0]->id, $in->id, $to->id, 1));
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seets[0]->id, $from->id, $in->id, 1));
    }
}
