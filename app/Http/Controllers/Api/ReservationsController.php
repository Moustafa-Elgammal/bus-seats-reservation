<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookSeatRequest;
use App\Http\Requests\GetTripsAvailableSeatsRequest;
use App\Services\Reservations\Interfaces\ReservationInterface;
use Illuminate\Support\Facades\Auth;

class ReservationsController extends Controller
{
    public function __construct(private readonly ReservationInterface $reservationService) {}

    public function getTripSeats(GetTripsAvailableSeatsRequest $request)
    {
        $trip = (int) $request->trip_id;
        $from = (int) $request->from_city_id;
        $to = (int) $request->to_city_id;

        $seats_ids = $this->reservationService->getAvailableSeatsOfTrip($trip, $from, $to);

        return response()->json([
            'data' => $seats_ids,
            'message' => '',
            'errors' => [],
            'okay' => true,
        ]);
    }

    public function bookSeat(BookSeatRequest $request)
    {
        $trip = (int) $request->trip_id;
        $seat = (int) $request->seat_id;
        $from = (int) $request->from_city_id;
        $to = (int) $request->to_city_id;

        $check = $this->reservationService->bookSeat($trip, $seat, $from, $to, Auth::id());

        $message = $check ? __('created') : __('can not be created');
        $status = $check ? 200 : 422;

        return response()->json([
            'data' => [],
            'message' => $message,
            'errors' => $check ? [] : [$message],
            'okay' => $check,
        ], $status);
    }
}
