<?php

namespace Tests\Feature\Trips;

use App\Models\City;
use App\Models\Trip;
use App\Models\TripSeat;
use App\Models\TripStation;
use App\Models\User;
use App\Services\Reservations\Interfaces\ReservationInterface;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsTrips;
use Tests\TestCase;

class SchemaConstraintsTest extends TestCase
{
    use BuildsTrips, RefreshDatabase;

    public function test_a_city_can_appear_only_once_on_a_route()
    {
        $trip = Trip::factory()->create();
        $city = City::factory()->create();

        TripStation::create(['trip_id' => $trip->id, 'city_id' => $city->id, 'station_order' => 1]);

        $this->expectException(QueryException::class);

        TripStation::create(['trip_id' => $trip->id, 'city_id' => $city->id, 'station_order' => 2]);
    }

    public function test_two_stops_of_a_trip_can_not_share_a_position()
    {
        $trip = Trip::factory()->create();

        TripStation::create(['trip_id' => $trip->id, 'city_id' => City::factory()->create()->id, 'station_order' => 1]);

        $this->expectException(QueryException::class);

        TripStation::create(['trip_id' => $trip->id, 'city_id' => City::factory()->create()->id, 'station_order' => 1]);
    }

    public function test_seat_numbers_are_unique_within_a_trip()
    {
        $trip = Trip::factory()->create();

        $this->expectException(QueryException::class);

        TripSeat::create(['trip_id' => $trip->id, 'seat_no' => $trip->seats->first()->seat_no]);
    }

    public function test_deleting_a_trip_cascades_to_stations_seats_and_reservations()
    {
        [$trip, $cities] = $this->makeTrip(capacity: 3);

        $booked = app(ReservationInterface::class)->bookSeat(
            $trip->id,
            $trip->seats->first()->id,
            $cities['Cairo']->id,
            $cities['Asyut']->id,
            User::factory()->create()->id,
        );

        $this->assertTrue($booked);

        $trip->delete();

        $this->assertDatabaseCount('trips_stations', 0);
        $this->assertDatabaseCount('trips_seats', 0);
        $this->assertDatabaseCount('customers_seats_reservations', 0);
        $this->assertDatabaseCount('reservations_stops', 0);
    }

    public function test_a_bus_that_still_runs_trips_can_not_be_deleted()
    {
        $trip = Trip::factory()->create();

        $this->expectException(QueryException::class);

        $trip->bus->delete();
    }
}
