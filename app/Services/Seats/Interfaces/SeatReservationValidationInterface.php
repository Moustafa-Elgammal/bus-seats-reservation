<?php

namespace App\Services\Seats\Interfaces;

interface SeatReservationValidationInterface
{
    /** check trip with $tid is the parent of the seat with $sid
     * @param $sid
     * @param $tid
     * @return bool
     */
    public static  function checkSeatBelognToTrip($sid, $tid): bool;


    /** check if a seat not available with some cities
     * @param $seatId
     * @param $needed_stations
     * @return bool
     */
    public static function checkSeatReservations($seatId, $needed_stations): bool;
}
