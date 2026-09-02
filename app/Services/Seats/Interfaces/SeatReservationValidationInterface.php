<?php

namespace App\Services\Seats\Interfaces;

interface SeatReservationValidationInterface
{
    /**
     * Whether the seat with $seatId belongs to the trip with $tripId.
     */
    public function checkSeatBelongsToTrip(int $seatId, int $tripId): bool;

    /**
     * Whether the seat is free for every city in the requested leg.
     *
     * @param  list<int>  $neededCities
     */
    public function checkSeatReservations(int $seatId, array $neededCities): bool;
}
