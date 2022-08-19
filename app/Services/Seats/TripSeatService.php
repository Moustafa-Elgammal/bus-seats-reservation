<?php

namespace App\Services\Seats;

use App\Models\ReservationsStop;
use App\Models\TripsSeat;
use App\Services\Seats\Interfaces\SeatReservationValidationInterface;
use App\Services\Seats\Interfaces\TripSeatServiceInterface;

class TripSeatService implements  TripSeatServiceInterface
{
    /** get the seats of trip
     * @param $tripId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static  function getTripSeats($tripId){
        return TripsSeat::query()
            ->where('trip_id', '=', $tripId)
            ->with('reservations')
            ->get();
    }

    /**
     * @param $seatId
     * @param $needed_stations
     * @return bool
     */
    public static function checkSeatReservations($seatId, $needed_stations): bool
    {
        return !ReservationsStop::query()
            ->leftJoin('customers_seats_reservations',
                'customers_seats_reservations.id', '=', 'reservations_stops.reservation_id')
            ->where('customers_seats_reservations.seat_id','=', $seatId)
            ->whereIn('reservations_stops.city_id', $needed_stations)->count();
    }

    /** check a seat of a trip
     * @param $sid
     * @param $tid
     * @return bool
     */
    public static function checkSeatBelognToTrip($sid, $tid): bool
    {
        // seat validation
        if(! $seat = TripsSeat::find($sid))
            return false;

        // seat trip id
        if ($seat->trip_id == $tid)
            return true;

        return false;
    }
}
