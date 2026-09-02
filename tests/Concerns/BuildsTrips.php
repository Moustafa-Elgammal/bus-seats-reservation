<?php

namespace Tests\Concerns;

use App\Models\City;
use App\Models\Trip;
use App\Models\TripStation;
use Illuminate\Support\Collection;

trait BuildsTrips
{
    /**
     * A trip on a bus of $capacity seats, running $cityNames in that order.
     * The returned collection is keyed by city name, so a test can name the
     * legs it books instead of juggling ids.
     *
     * @param  list<string>  $cityNames
     * @return array{0: Trip, 1: Collection<string, City>}
     */
    protected function makeTrip(array $cityNames = ['Cairo', 'AlMinya', 'Asyut'], int $capacity = 12): array
    {
        $trip = Trip::factory()->capacity($capacity)->withRoute($cityNames)->create();

        $cities = $trip->stations()
            ->with('city')
            ->orderBy('station_order')
            ->get()
            ->mapWithKeys(fn (TripStation $station): array => [$station->city->name => $station->city]);

        return [$trip, $cities];
    }
}
