<?php

namespace App\Services\Trips\Interfaces;

interface TripReservationFromToValidationInterface
{
    /**
     * Whether from -> to is a valid leg of the given trip's route.
     */
    public function validateNeededTripRoute(int $tripId, int $fromCityId, int $toCityId): bool;
}
