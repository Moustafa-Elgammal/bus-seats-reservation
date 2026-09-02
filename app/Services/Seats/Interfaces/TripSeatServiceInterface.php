<?php

namespace App\Services\Seats\Interfaces;

use App\Models\TripSeat;
use Illuminate\Database\Eloquent\Collection;

interface TripSeatServiceInterface extends SeatReservationValidationInterface
{
    /**
     * All seats belonging to the given trip.
     *
     * @return Collection<int, TripSeat>
     */
    public function getTripSeats(int $tripId): Collection;
}
