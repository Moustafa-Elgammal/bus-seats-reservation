<?php

namespace Tests\Feature\Admin;

use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripStation;
use App\Models\User;
use App\Services\Reservations\Interfaces\ReservationInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripsCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    /**
     * @return array{0: Trip, 1: Collection<int, City>}
     */
    private function makeRoutedTrip(int $capacity = 3): array
    {
        $trip = Trip::factory()->create(['bus_id' => Bus::factory()->create(['seats_capacity' => $capacity])]);

        $cities = City::factory()->count(3)->create();
        $cities->each(fn (City $city, int $order) => TripStation::create([
            'trip_id' => $trip->id,
            'city_id' => $city->id,
            'station_order' => $order,
        ]));

        return [$trip, $cities];
    }

    private function book(Trip $trip, Collection $cities): void
    {
        $booked = app(ReservationInterface::class)->bookSeat(
            $trip->id,
            $trip->seats->first()->id,
            $cities[0]->id,
            $cities[2]->id,
            User::factory()->create()->id,
        );

        $this->assertTrue($booked);
    }

    public function test_trips_page_renders()
    {
        $this->makeRoutedTrip();

        $this->get(route('trips'))->assertOk();
    }

    public function test_admin_can_rename_a_trip()
    {
        [$trip] = $this->makeRoutedTrip();

        $this->put(route('trip.update', $trip), ['name' => 'Cairo Asyut Express'])
            ->assertSessionHas('success');

        $this->assertSame('Cairo Asyut Express', $trip->fresh()->name);
    }

    public function test_deleting_a_trip_removes_its_route_and_seats()
    {
        [$trip] = $this->makeRoutedTrip();

        $this->delete(route('trip.destroy', $trip))->assertSessionHas('success');

        $this->assertDatabaseMissing('trips', ['id' => $trip->id]);
        $this->assertDatabaseCount('trips_stations', 0);
        $this->assertDatabaseCount('trips_seats', 0);
    }

    public function test_deleting_a_trip_with_reservations_is_refused()
    {
        [$trip, $cities] = $this->makeRoutedTrip();
        $this->book($trip, $cities);

        $this->delete(route('trip.destroy', $trip))->assertSessionHas('service_errors');

        $this->assertDatabaseHas('trips', ['id' => $trip->id]);
    }

    public function test_admin_can_remove_a_stop_from_an_unsold_trip()
    {
        [$trip] = $this->makeRoutedTrip();
        $station = $trip->stations()->orderByDesc('station_order')->first();

        $this->delete(route('trip.station.destroy', $station))->assertSessionHas('success');

        $this->assertDatabaseMissing('trips_stations', ['id' => $station->id]);
    }

    public function test_removing_a_stop_from_a_trip_with_reservations_is_refused()
    {
        [$trip, $cities] = $this->makeRoutedTrip();
        $this->book($trip, $cities);
        $station = $trip->stations()->orderByDesc('station_order')->first();

        $this->delete(route('trip.station.destroy', $station))->assertSessionHas('service_errors');

        $this->assertDatabaseHas('trips_stations', ['id' => $station->id]);
    }
}
