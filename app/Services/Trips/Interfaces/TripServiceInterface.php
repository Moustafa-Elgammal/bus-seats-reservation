<?php

namespace App\Services\Trips\Interfaces;

interface TripServiceInterface extends TripReservationFromToValidationInterface
{
    /** stations of a trip
     * @param $tripId
     * @return array
     */
    public function getTripStationsOrders($tripId): array;

    public function getNeededStopsFromTrip($tripId, $fromCityId, $toCityId): array;
}
