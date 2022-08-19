<?php

namespace App\Services\Trips;

use App\Models\TripsStation;
use App\Services\Trips\Interfaces\TripReservationFromToValidationInterface;
use App\Services\Trips\Interfaces\TripServiceInterface;

class TripService implements TripServiceInterface
{
    /** get the trip route stations
     * @param $tripId
     * @return array
     */
    public function getTripStationsOrders($tripId): array
    {
        return TripsStation::query()
            ->where('trip_id', '=', $tripId)
            ->orderBy('station_order')
            ->pluck('city_id')
            ->toArray();
    }

    /** check the arrival and departure station for a trip
     * @param $tripId
     * @param $fromCityId
     * @param $toCityId
     * @return bool
     */
    public function validateNeededTripRoute($tripId, $fromCityId, $toCityId): bool
    {

        // the city of arrival can not be the same as  city of  departure station
        if ($fromCityId == $toCityId)
            return false;

        // get the stations route
        $trip_stations = $this->getTripStationsOrders($tripId);

        // check that from to is included the trip
        if (!in_array($fromCityId, $trip_stations) || !in_array($toCityId, $trip_stations))
            return false;

        /**
         * check the rout of the stations departure
         */
        $from = array_search($fromCityId, $trip_stations);
        $to = array_search($toCityId, $trip_stations);

        // the city of arrival can not be before the city of  departure station or the same station
        if ($from >= $to)
            return  false;

        return true;
    }

    /** this method help to generate the need stations for a selected trip
     * to use for create reservation stops and checking
     * @param $tripId
     * @param $fromCityId
     * @param $toCityId
     * @return bool
     */
    public function getNeededStopsFromTrip($tripId, $fromCityId, $toCityId): array
    {
        // validate the correct trips and needed from to
        if (!$this->validateNeededTripRoute($tripId, $fromCityId, $toCityId)) return [];

        // get the stations route
        $trip_stations = $this->getTripStationsOrders($tripId);

        // identify the positions of the stations
        $from = array_search($fromCityId, $trip_stations);
        $to = array_search($toCityId, $trip_stations);

        // return the stations of a selected trip from to
        return array_slice($trip_stations, $from, $to - $from);
    }
}
