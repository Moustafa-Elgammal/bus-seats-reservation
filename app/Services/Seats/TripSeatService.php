<?php

namespace App\Services\Seats;

use App\Models\ReservationStop;
use App\Models\TripSeat;
use App\Services\Seats\Interfaces\TripSeatServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TripSeatService implements TripSeatServiceInterface
{
    /** get the seats of trip
     * @return Builder[]|Collection
     */
    public static function getTripSeats($tripId)
    {
        return TripSeat::query()
            ->where('trip_id', '=', $tripId)
            ->get();
    }

    public static function checkSeatReservations($seatId, $needed_stations): bool
    {
        return ! ReservationStop::query()
            ->leftJoin('customers_seats_reservations',
                'customers_seats_reservations.id', '=', 'reservations_stops.reservation_id')
            ->where('customers_seats_reservations.seat_id', '=', $seatId)
            ->whereIn('reservations_stops.city_id', $needed_stations)->count();
    }

    /** check a seat of a trip
     */
    public static function checkSeatBelognToTrip($sid, $tid): bool
    {
        // seat validation
        if (! $seat = TripSeat::find($sid)) {
            return false;
        }

        // seat trip id
        if ($seat->trip_id == $tid) {
            return true;
        }

        return false;
    }
}
