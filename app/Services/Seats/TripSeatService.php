<?php

namespace App\Services\Seats;

use App\Models\ReservationStop;
use App\Models\TripSeat;
use App\Services\Seats\Interfaces\TripSeatServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class TripSeatService implements TripSeatServiceInterface
{
    /**
     * @return Collection<int, TripSeat>
     */
    public function getTripSeats(int $tripId): Collection
    {
        return TripSeat::query()
            ->where('trip_id', $tripId)
            ->get();
    }

    /**
     * True when the seat has no reservation stop on any city of the requested leg.
     *
     * @param  list<int>  $neededCities
     */
    public function checkSeatReservations(int $seatId, array $neededCities): bool
    {
        return ! ReservationStop::query()
            ->where('seat_id', $seatId)
            ->whereIn('city_id', $neededCities)
            ->exists();
    }

    public function checkSeatBelongsToTrip(int $seatId, int $tripId): bool
    {
        return TripSeat::query()
            ->whereKey($seatId)
            ->where('trip_id', $tripId)
            ->exists();
    }
}
