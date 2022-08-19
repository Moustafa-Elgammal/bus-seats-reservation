<?php

namespace App\Services\Trips\Interfaces;

interface TripReservationFromToValidationInterface
{
    /** check if seat can be booked for specific city
     * @param $tripId
     * @param $fromCityId
     * @param $toCityId
     * @return bool
     */
    public function validateNeededTripRoute($tripId, $fromCityId, $toCityId): bool;
}
