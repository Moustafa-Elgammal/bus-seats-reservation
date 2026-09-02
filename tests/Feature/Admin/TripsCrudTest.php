<?php

namespace Tests\Feature\Admin;

use App\Models\Trip;
use App\Models\User;
use App\Services\Reservations\Interfaces\ReservationInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\Concerns\BuildsTrips;
use Tests\TestCase;

class TripsCrudTest extends TestCase
{
    use BuildsTrips, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    private function book(Trip $trip, Collection $cities): void
    {
        $booked = app(ReservationInterface::class)->bookSeat(
            $trip->id,
            $trip->seats->first()->id,
            $cities['Cairo']->id,
            $cities['Asyut']->id,
            User::factory()->create()->id,
        );

        $this->assertTrue($booked);
    }

    public function test_trips_page_renders()
    {
        $this->makeTrip(capacity: 3);

        $this->get(route('trips'))->assertOk();
    }

    public function test_admin_can_rename_a_trip()
    {
        [$trip] = $this->makeTrip(capacity: 3);

        $this->put(route('trip.update', $trip), ['name' => 'Cairo Asyut Express'])
            ->assertSessionHas('success');

        $this->assertSame('Cairo Asyut Express', $trip->fresh()->name);
    }

    public function test_deleting_a_trip_removes_its_route_and_seats()
    {
        [$trip] = $this->makeTrip(capacity: 3);

        $this->delete(route('trip.destroy', $trip))->assertSessionHas('success');

        $this->assertDatabaseMissing('trips', ['id' => $trip->id]);
        $this->assertDatabaseCount('trips_stations', 0);
        $this->assertDatabaseCount('trips_seats', 0);
    }

    public function test_deleting_a_trip_with_reservations_is_refused()
    {
        [$trip, $cities] = $this->makeTrip(capacity: 3);
        $this->book($trip, $cities);

        $this->delete(route('trip.destroy', $trip))->assertSessionHas('service_errors');

        $this->assertDatabaseHas('trips', ['id' => $trip->id]);
    }

    public function test_admin_can_remove_a_stop_from_an_unsold_trip()
    {
        [$trip] = $this->makeTrip(capacity: 3);
        $station = $trip->stations()->orderByDesc('station_order')->first();

        $this->delete(route('trip.station.destroy', $station))->assertSessionHas('success');

        $this->assertDatabaseMissing('trips_stations', ['id' => $station->id]);
    }

    public function test_removing_a_stop_from_a_trip_with_reservations_is_refused()
    {
        [$trip, $cities] = $this->makeTrip(capacity: 3);
        $this->book($trip, $cities);
        $station = $trip->stations()->orderByDesc('station_order')->first();

        $this->delete(route('trip.station.destroy', $station))->assertSessionHas('service_errors');

        $this->assertDatabaseHas('trips_stations', ['id' => $station->id]);
    }
}
