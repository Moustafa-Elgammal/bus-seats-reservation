<?php

namespace Tests\Feature\Trips;

use App\Models\Bus;
use App\Models\Trip;
use App\Models\TripSeat;
use App\Observers\TripObserver;
use Database\Seeders\TripSeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class TripObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_trip_generates_one_seat_per_bus_capacity()
    {
        $bus = Bus::factory()->create(['seats_capacity' => 7]);

        $trip = Trip::factory()->create(['bus_id' => $bus->id]);

        $this->assertDatabaseCount('trips_seats', 7);
        $this->assertSame(7, $trip->seats()->count());
        $this->assertTrue($trip->seats->every(fn (TripSeat $seat) => $seat->trip_id === $trip->id));
    }

    public function test_created_throws_when_the_trip_has_no_bus()
    {
        $this->expectException(RuntimeException::class);

        (new TripObserver)->created(new Trip);
    }

    public function test_seed_generates_seats()
    {
        $this->seed(TripSeed::class);

        $this->assertGreaterThan(0, TripSeat::count());
    }
}
