<?php

namespace App\Observers;

use App\Models\Trip;
use App\Models\TripSeat;
use RuntimeException;

class TripObserver
{
    /**
     * Generate one trips_seats row per seat of the trip's bus.
     *
     * Runs inside whatever transaction created the Trip (see TripsController and
     * TripSeed) so a failure here rolls the Trip back with it. Failures are not
     * swallowed.
     */
    public function created(Trip $trip): void
    {
        $bus = $trip->bus;

        if ($bus === null) {
            throw new RuntimeException("Trip {$trip->id} has no bus; cannot generate seats.");
        }

        $capacity = max((int) $bus->seats_capacity, 0);

        if ($capacity === 0) {
            return;
        }

        $now = now();

        TripSeat::query()->insert(array_map(fn (): array => [
            'trip_id' => $trip->id,
            'created_at' => $now,
            'updated_at' => $now,
        ], range(1, $capacity)));
    }
}
