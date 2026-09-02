<?php

namespace App\Services\Reservations\Interfaces;

interface ReservationInterface
{
    /**
     * Ids of the seats still bookable on the given leg.
     *
     * @return list<int>
     */
    public function getAvailableSeatsOfTrip(int $tripId, int $fromCityId, int $toCityId): array;

    /**
     * Book one seat for the given leg. Returns false when the seat is not on the
     * trip, the route is invalid, or the seat is already taken on an overlapping leg.
     */
    public function bookSeat(int $tripId, int $seatId, int $fromCityId, int $toCityId, int $userId): bool;
}
