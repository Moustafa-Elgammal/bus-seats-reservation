<?php

namespace App\Services\Trips;

use App\Models\TripStation;
use App\Services\Trips\Interfaces\TripServiceInterface;

class TripService implements TripServiceInterface
{
    /**
     * Ordered city ids that make up the trip's route.
     *
     * @return list<int>
     */
    public function getTripStationsOrders(int $tripId): array
    {
        return TripStation::query()
            ->where('trip_id', $tripId)
            ->orderBy('station_order')
            ->pluck('city_id')
            ->map(fn ($cityId): int => (int) $cityId)
            ->all();
    }

    /**
     * A leg is valid when from and to differ, both are on the route, and from
     * comes strictly before to.
     */
    public function validateNeededTripRoute(int $tripId, int $fromCityId, int $toCityId): bool
    {
        if ($fromCityId === $toCityId) {
            return false;
        }

        $tripStations = $this->getTripStationsOrders($tripId);

        if (! in_array($fromCityId, $tripStations, true) || ! in_array($toCityId, $tripStations, true)) {
            return false;
        }

        $from = array_search($fromCityId, $tripStations, true);
        $to = array_search($toCityId, $tripStations, true);

        return $from < $to;
    }

    /**
     * The leg's occupied city ids: from-city inclusive, to-city exclusive.
     * An empty array means the requested leg is invalid.
     *
     * @return list<int>
     */
    public function getNeededStopsFromTrip(int $tripId, int $fromCityId, int $toCityId): array
    {
        if (! $this->validateNeededTripRoute($tripId, $fromCityId, $toCityId)) {
            return [];
        }

        $tripStations = $this->getTripStationsOrders($tripId);

        $from = array_search($fromCityId, $tripStations, true);
        $to = array_search($toCityId, $tripStations, true);

        return array_slice($tripStations, $from, $to - $from);
    }
}
