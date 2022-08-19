<?php

namespace App\Services\Seats\Interfaces;

interface TripSeatServiceInterface extends SeatReservationValidationInterface
{
    /** get seat of trip
     * @param $tripId
     * @return mixed
     */
    public static  function getTripSeats($tripId);
}
