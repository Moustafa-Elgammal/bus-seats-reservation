<?php

namespace Tests\Feature\Reservations;

use App\Models\City;
use App\Services\Trips\Interfaces\TripServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsTrips;
use Tests\TestCase;

class TripTest extends TestCase
{
    use BuildsTrips, RefreshDatabase;

    private TripServiceInterface $tripService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tripService = app(TripServiceInterface::class);
    }

    public function test_get_trip_stations_orders()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);

        // strict: exact ints, in route order
        $this->assertSame(
            [$c['Cairo']->id, $c['AlMinya']->id, $c['Asyut']->id],
            $this->tripService->getTripStationsOrders($trip->id),
        );
    }

    public function test_get_needed_stops_from_trip()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'Giza', 'AlFayyum', 'AlMinya', 'Asyut']);

        // strict: a plain list of ints (from inclusive, to exclusive)
        $this->assertSame(
            [$c['Cairo']->id],
            $this->tripService->getNeededStopsFromTrip($trip->id, $c['Cairo']->id, $c['Giza']->id),
        );
        $this->assertSame(
            [$c['Cairo']->id, $c['Giza']->id],
            $this->tripService->getNeededStopsFromTrip($trip->id, $c['Cairo']->id, $c['AlFayyum']->id),
        );
    }

    public function test_get_needed_stops_from_trip_returns_nothing_for_an_invalid_leg()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);

        $this->assertSame([], $this->tripService->getNeededStopsFromTrip($trip->id, $c['Asyut']->id, $c['Cairo']->id));
    }

    public function test_validate_route_trip()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'Giza', 'AlFayyum', 'AlMinya', 'Asyut']);
        $offRoute = City::factory()->create(['name' => 'Aswan']);

        // same city on both ends
        $this->assertFalse($this->tripService->validateNeededTripRoute($trip->id, $c['Cairo']->id, $c['Cairo']->id));
        $this->assertFalse($this->tripService->validateNeededTripRoute($trip->id, $c['Giza']->id, $c['Giza']->id));

        // cities that are not on this route
        $this->assertFalse($this->tripService->validateNeededTripRoute($trip->id, $offRoute->id, $c['Asyut']->id));
        $this->assertFalse($this->tripService->validateNeededTripRoute($trip->id, $c['Cairo']->id, $offRoute->id));

        // reversed route
        $this->assertFalse($this->tripService->validateNeededTripRoute($trip->id, $c['Asyut']->id, $c['Cairo']->id));

        // normal case
        $this->assertTrue($this->tripService->validateNeededTripRoute($trip->id, $c['Cairo']->id, $c['Asyut']->id));
    }
}
