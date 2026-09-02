<?php

namespace Tests\Feature\Reservations;

use App\Models\Reservation;
use App\Models\ReservationStop;
use App\Models\User;
use App\Services\Reservations\Interfaces\ReservationInterface;
use App\Services\Seats\Interfaces\TripSeatServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Concerns\BuildsTrips;
use Tests\TestCase;

class ReservationsTest extends TestCase
{
    use BuildsTrips, RefreshDatabase;

    private ReservationInterface $reservationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reservationService = app(ReservationInterface::class);
    }

    public function test_get_available_seats_of_trip()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut'], capacity: 12);
        $seats = $trip->seats;
        $user = User::factory()->create();

        $this->assertCount(
            12,
            $this->reservationService->getAvailableSeatsOfTrip($trip->id, $c['Cairo']->id, $c['Asyut']->id),
        );

        $this->reservationService->bookSeat($trip->id, $seats[2]->id, $c['Cairo']->id, $c['Asyut']->id, $user->id);

        $this->assertCount(
            11,
            $this->reservationService->getAvailableSeatsOfTrip($trip->id, $c['Cairo']->id, $c['Asyut']->id),
        );
        $this->assertCount(
            11,
            $this->reservationService->getAvailableSeatsOfTrip($trip->id, $c['AlMinya']->id, $c['Asyut']->id),
        );
    }

    public function test_book_seat_allows_resale_on_disjoint_legs_only()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);
        $seats = $trip->seats;
        $user = User::factory()->create();

        // full route
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seats[2]->id, $c['Cairo']->id, $c['Asyut']->id, $user->id));
        // overlapping leg on the same seat is refused
        $this->assertFalse($this->reservationService->bookSeat($trip->id, $seats[2]->id, $c['Cairo']->id, $c['Asyut']->id, $user->id));

        // two disjoint halves on one seat
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seats[1]->id, $c['Cairo']->id, $c['AlMinya']->id, $user->id));
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seats[1]->id, $c['AlMinya']->id, $c['Asyut']->id, $user->id));

        // same, second half first
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seats[0]->id, $c['AlMinya']->id, $c['Asyut']->id, $user->id));
        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seats[0]->id, $c['Cairo']->id, $c['AlMinya']->id, $user->id));
    }

    public function test_double_booking_same_leg_is_rejected_and_leaves_no_partial_rows()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);
        $seatId = $trip->seats[2]->id;
        $user = User::factory()->create();

        $this->assertTrue($this->reservationService->bookSeat($trip->id, $seatId, $c['Cairo']->id, $c['Asyut']->id, $user->id));
        $this->assertFalse($this->reservationService->bookSeat($trip->id, $seatId, $c['Cairo']->id, $c['AlMinya']->id, $user->id));

        $this->assertDatabaseCount('customers_seats_reservations', 1);
        $this->assertDatabaseCount('reservations_stops', 2); // only the first booking's legs
    }

    public function test_reservation_stops_are_denormalised_with_seat_id()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);
        $seatId = $trip->seats[0]->id;

        $this->reservationService->bookSeat($trip->id, $seatId, $c['Cairo']->id, $c['Asyut']->id, User::factory()->create()->id);

        $this->assertDatabaseHas('reservations_stops', ['seat_id' => $seatId, 'city_id' => $c['Cairo']->id]);
        $this->assertDatabaseHas('reservations_stops', ['seat_id' => $seatId, 'city_id' => $c['AlMinya']->id]);
    }

    public function test_book_seat_returns_false_when_the_unique_index_rejects_a_lost_race()
    {
        // sqlite :memory: is single-connection, so a genuine concurrent booking
        // can't be exercised. Stub the availability check to "free" so bookSeat
        // proceeds to the insert, plant the clashing reservations_stops row, and
        // assert the unique(seat_id, city_id) violation is swallowed and the
        // half-written reservation is rolled back.
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);
        $seatId = $trip->seats[1]->id;
        $user = User::factory()->create();

        $existing = Reservation::forceCreate(['user_id' => $user->id, 'seat_id' => $seatId]);
        ReservationStop::create(['reservation_id' => $existing->id, 'seat_id' => $seatId, 'city_id' => $c['Cairo']->id]);

        $seatService = Mockery::mock(TripSeatServiceInterface::class);
        $seatService->shouldReceive('checkSeatBelongsToTrip')->andReturnTrue();
        $seatService->shouldReceive('checkSeatReservations')->andReturnTrue();
        $this->app->instance(TripSeatServiceInterface::class, $seatService);

        $this->assertFalse(
            app(ReservationInterface::class)
                ->bookSeat($trip->id, $seatId, $c['Cairo']->id, $c['Asyut']->id, $user->id)
        );
        $this->assertDatabaseCount('customers_seats_reservations', 1); // no new row
        $this->assertDatabaseCount('reservations_stops', 1);
    }
}
