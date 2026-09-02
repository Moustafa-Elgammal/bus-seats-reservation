<?php

namespace Tests\Feature\Reservations;

use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripStation;
use App\Models\User;
use App\Services\Reservations\ReservationService;
use App\Services\Trips\TripService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationsTest extends TestCase
{
    use RefreshDatabase;

    protected $reservationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reservationService = new ReservationService(new TripService);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_available_seats_of_trip()
    {
        // seed init cities
        $from = City::factory()->create(['name' => 'Cairo']);
        $in = City::factory()->create(['name' => 'AlMinya']);
        $to = City::factory()->create(['name' => 'Asyut']);

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

        // generate the trip seats
        $seets = $trip->seats;

        $this->assertEquals(
            count($this->reservationService->getAvailableSeatsOfTrip($trip->id, $from->id, $to->id)),
            $bus->seats_capacity);

        $user = User::factory()->create();
        // do reserve
        $this->reservationService->bookSeat($trip->id, $seets[2]->id, $from->id, $to->id, $user->id);

        $this->assertEquals(
            count($this->reservationService->getAvailableSeatsOfTrip($trip->id, $from->id, $to->id)),
            $bus->seats_capacity - 1);

        // do reserve
        $this->reservationService->bookSeat($trip->id, $seets[2]->id, $from->id, $in->id, $user->id);

        $this->assertEquals(
            count($this->reservationService->getAvailableSeatsOfTrip($trip->id, $in->id, $to->id)),
            $bus->seats_capacity - 1);

    }

    public function test_book_set()
    {
        // seed init cities
        $from = City::factory()->create(['name' => 'Cairo']);
        $in = City::factory()->create(['name' => 'AlMinya']);
        $to = City::factory()->create(['name' => 'Asyut']);

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

        // generate the trip seats
        $seets = $trip->seats;

        // get seats
        //

        $this->assertEquals(
            count($this->reservationService->getAvailableSeatsOfTrip($trip->id, $from->id, $to->id)),
            $bus->seats_capacity);

        $user = User::factory()->create();
        // first reservation of seat
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seets[2]->id, $from->id, $to->id, $user->id));

        // check second attempt of the same reservation
        $this->assertFalse($this->reservationService->bookSeat($trip->id, $seets[2]->id, $from->id, $to->id, $user->id));

        // reserve the half route
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seets[1]->id, $from->id, $in->id, $user->id));
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seets[1]->id, $in->id, $to->id, $user->id));

        // reserve last two station then first two station

        // reserve the half route
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seets[0]->id, $in->id, $to->id, $user->id));
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seets[0]->id, $from->id, $in->id, $user->id));
    }
}
