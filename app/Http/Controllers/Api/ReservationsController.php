<?php

namespace App\Http\Controllers\Api;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookSeatRequest;
use App\Http\Requests\GetTripsAvailableSeatsRequest;
use App\Services\Reservations\Interfaces\ReservationInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReservationsController extends Controller
{
    use ApiResponses;

    public function __construct(private readonly ReservationInterface $reservationService) {}

    public function getTripSeats(GetTripsAvailableSeatsRequest $request): JsonResponse
    {
        $seatIds = $this->reservationService->getAvailableSeatsOfTrip(
            (int) $request->trip_id,
            (int) $request->from_city_id,
            (int) $request->to_city_id,
        );

        return $this->apiOk($seatIds);
    }

    public function bookSeat(BookSeatRequest $request): JsonResponse
    {
        $booked = $this->reservationService->bookSeat(
            (int) $request->trip_id,
            (int) $request->seat_id,
            (int) $request->from_city_id,
            (int) $request->to_city_id,
            (int) Auth::id(),
        );

        return $booked
            ? $this->apiOk([], __('created'))
            : $this->apiFail(__('can not be created'), [__('can not be created')]);
    }
}
