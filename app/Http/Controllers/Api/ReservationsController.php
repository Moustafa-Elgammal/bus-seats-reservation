<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookSeatRequest;
use App\Http\Requests\GetTripsAvailableSeatsRequest;
use App\Services\Reservations\ReservationService;
use App\Services\Trips\TripService;
use Illuminate\Support\Facades\Auth;

class ReservationsController extends Controller
{
    public function __construct()
    {
        $this->reservationService = new ReservationService(new TripService());
    }

    public function getTripSeats(GetTripsAvailableSeatsRequest $request)
    {
        $trip = (int)$request->trip_id;
        $from = (int)$request->from_city_id;
        $to = (int)$request->to_city_id;

        $seats_ids = $this->reservationService->getAvailableSeatsOfTrip($trip, $from, $to);

        return response()->json([
            'data' => $seats_ids,
            'message' => '',
            'errors' => [],
            'okay' => true
        ]);
    }

    public function book_seat(BookSeatRequest $request)
    {
        $trip = (int)$request->trip_id;
        $seat = (int)$request->seat_id;
        $from = (int)$request->from_city_id;
        $to = (int)$request->to_city_id;

        $user_id = Auth::id() ?? 1;

        $check =  $this->reservationService->bookSeat($trip, $seat, $from, $to,$user_id);

        $message = $check ? __("created"): __("can not be created");

        $status = $check? 200 : 422;
        return response()->json([
            'data' => [],
            'message' => $message,
            'errors' => ["$message"],
            'okay' => $check
        ], $status);
    }


}
