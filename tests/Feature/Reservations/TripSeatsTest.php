<?php

namespace Tests\Feature\Reservations;

use App\Models\User;
use App\Services\Reservations\Interfaces\ReservationInterface;
use App\Services\Seats\Interfaces\TripSeatServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsTrips;
use Tests\TestCase;

class TripSeatsTest extends TestCase
{
    use BuildsTrips, RefreshDatabase;

    private TripSeatServiceInterface $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TripSeatServiceInterface::class);
    }

    public function test_get_trip_seats_returns_every_seat_of_the_trip()
    {
        [$trip] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut'], capacity: 5);

        $this->assertEqualsCanonicalizing(
            $trip->seats->pluck('id')->all(),
            $this->service->getTripSeats($trip->id)->pluck('id')->all(),
        );
    }

    public function test_check_seat_reservation()
    {
        [$trip, $cities] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);
        $seats = $trip->seats;

        app(ReservationInterface::class)->bookSeat(
            $trip->id, $seats[2]->id, $cities['Cairo']->id, $cities['Asyut']->id, User::factory()->create()->id,
        );

        // the booked seat is no longer free for the overlapping Cairo->AlMinya leg
        $this->assertFalse($this->service->checkSeatReservations(
            $seats[2]->id, [$cities['Cairo']->id, $cities['AlMinya']->id],
        ));

        // an untouched seat still is
        $this->assertTrue($this->service->checkSeatReservations(
            $seats[1]->id, [$cities['Cairo']->id, $cities['AlMinya']->id],
        ));
    }

    public function test_check_seat_belongs_to_trip()
    {
        [$trip] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);
        $seatId = $trip->seats->first()->id;

        $this->assertTrue($this->service->checkSeatBelongsToTrip($seatId, $trip->id));
        $this->assertFalse($this->service->checkSeatBelongsToTrip($seatId, $trip->id + 1));
    }
}
