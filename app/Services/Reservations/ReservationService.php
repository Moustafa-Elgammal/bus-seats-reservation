<?php

namespace App\Services\Reservations;

use App\Models\Reservations;
use App\Models\ReservationsStop;
use App\Services\Reservations\Interfaces\ReservationInterface;
use App\Services\Seats\TripSeatService;
use App\Services\Trips\TripService;
use Illuminate\Support\Facades\DB;

class ReservationService implements ReservationInterface
{

    /**
     * @param TripService $tripService
     */
    public function __construct(protected TripService $tripService){}

    /** generate array of available seats
     * @param $tripId
     * @param $fromCityId
     * @param $toCityId
     * @return array
     */
    public function getAvailableSeatsOfTrip($tripId, $fromCityId, $toCityId): array
    {

        //get all trip seats
        $trip_seats = TripSeatService::getTripSeats($tripId);

        //get the needed stops between from and to cities
        $needed_stations = $this->tripService->getNeededStopsFromTrip($tripId, $fromCityId, $toCityId);

        // no stop selection no station will be needed
        if (empty($needed_stations))
            return [];

        $seats = [];
        foreach ($trip_seats as $seat){
            // check seat for the user route stations
            if (TripSeatService::checkSeatReservations($seat->id, $needed_stations))
                $seats[] = $seat->id;
        }

        return  $seats;
    }

    /** create reservation
     * @param $tripId
     * @param $seatId
     * @param $fromCityId
     * @param $toCityId
     * @return bool
     */
    public function bookSeat($tripId, $seatId, $fromCityId, $toCityId, $user_id): bool
    {
        //check seat relation with the selected trip
        if (!TripSeatService::checkSeatBelognToTrip($seatId, $tripId))
            return false;


          // get the reservations stop
        $needed_stops = $this->tripService->getNeededStopsFromTrip($tripId, $fromCityId, $toCityId);

        // check if this reservation has no stations
        if (empty($needed_stops))
            return false;

        // check seat validation
        if (!TripSeatService::checkSeatReservations($seatId, $needed_stops))
            return false;


        /*
         * use transactions when changes in many tables and once may be failed
         * so you need to rollback
         */
        DB::beginTransaction();

        // create new reservation
        $reservation = new Reservations();
        $reservation->user_id = 1; // TODO ::userid Auth
        $reservation->seat_id = $seatId;

        try {

            // check database saving
            if($reservation->save()){
                foreach ($needed_stops as $cityId){
                    ReservationsStop::create([
                        'reservation_id' => $reservation->id,
                        'city_id' => $cityId,
                    ]);
                }

                DB::commit();
                return true;
            }

            DB::rollBack();
            return false;

        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }
}
