<?php

namespace App\Observers;

use App\Models\Trip;
use App\Models\TripsSeat;

class TripObserver
{
    /**
     * Handle the Trip "created" event.
     *
     * @param  \App\Models\Trip  $trip
     * @return void
     */
    public function created(Trip $trip)
    {
        // generate trip seats
        try {
            TripsSeat::factory($trip->bus->seats_capacity)->create([
                "trip_id" => $trip->id
            ]);
        }catch (\Exception $e){
            logger('Trip observer can not generate trip seats');
        }

    }

    /**
     * Handle the Trip "updated" event.
     *
     * @param  \App\Models\Trip  $trip
     * @return void
     */
    public function updated(Trip $trip)
    {
        //
    }

    /**
     * Handle the Trip "deleted" event.
     *
     * @param  \App\Models\Trip  $trip
     * @return void
     */
    public function deleted(Trip $trip)
    {
        //
    }

    /**
     * Handle the Trip "restored" event.
     *
     * @param  \App\Models\Trip  $trip
     * @return void
     */
    public function restored(Trip $trip)
    {
        //
    }

    /**
     * Handle the Trip "force deleted" event.
     *
     * @param  \App\Models\Trip  $trip
     * @return void
     */
    public function forceDeleted(Trip $trip)
    {
        //
    }
}
