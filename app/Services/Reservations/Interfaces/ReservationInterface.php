<?php

namespace App\Services\Reservations\Interfaces;

interface ReservationInterface
{
    public function getAvailableSeatsOfTrip($tripId, $fromCityId, $toCityId);

    public function bookSeat($tripId, $seatId, $fromCityId, $toCityId, $user_id);
}
