<?php

namespace App\Services\Trips\Interfaces;

interface TripServiceInterface extends TripReservationFromToValidationInterface
{
    /**
     * Ordered city ids that make up the trip's route.
     *
     * @return list<int>
     */
    public function getTripStationsOrders(int $tripId): array;

    /**
     * The leg's occupied city ids (from inclusive, to exclusive); [] if invalid.
     *
     * @return list<int>
     */
    public function getNeededStopsFromTrip(int $tripId, int $fromCityId, int $toCityId): array;
}
