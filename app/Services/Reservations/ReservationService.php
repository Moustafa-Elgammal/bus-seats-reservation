<?php

namespace App\Services\Reservations;

use App\Models\Reservation;
use App\Models\ReservationStop;
use App\Models\TripSeat;
use App\Services\Reservations\Interfaces\ReservationInterface;
use App\Services\Seats\Interfaces\TripSeatServiceInterface;
use App\Services\Trips\Interfaces\TripServiceInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ReservationService implements ReservationInterface
{
    public function __construct(
        protected TripServiceInterface $tripService,
        protected TripSeatServiceInterface $tripSeatService,
    ) {}

    /**
     * @return list<int>
     */
    public function getAvailableSeatsOfTrip(int $tripId, int $fromCityId, int $toCityId): array
    {
        $neededStations = $this->tripService->getNeededStopsFromTrip($tripId, $fromCityId, $toCityId);

        if ($neededStations === []) {
            return [];
        }

        $tripSeatIds = $this->tripSeatService->getTripSeats($tripId)->pluck('id');

        $takenSeatIds = ReservationStop::query()
            ->whereIn('seat_id', $tripSeatIds)
            ->whereIn('city_id', $neededStations)
            ->distinct()
            ->pluck('seat_id');

        return $tripSeatIds->diff($takenSeatIds)->values()->all();
    }

    public function bookSeat(int $tripId, int $seatId, int $fromCityId, int $toCityId, int $userId): bool
    {
        if (! $this->tripSeatService->checkSeatBelongsToTrip($seatId, $tripId)) {
            return false;
        }

        $neededStops = $this->tripService->getNeededStopsFromTrip($tripId, $fromCityId, $toCityId);

        if ($neededStops === []) {
            return false;
        }

        try {
            return DB::transaction(function () use ($seatId, $userId, $neededStops): bool {
                // Serialise concurrent bookings of this seat; the re-check then
                // runs against a stable view, and the unique (seat_id, city_id)
                // index is the final backstop for the first-insert race.
                TripSeat::query()->whereKey($seatId)->lockForUpdate()->firstOrFail();

                if (! $this->tripSeatService->checkSeatReservations($seatId, $neededStops)) {
                    return false;
                }

                $reservation = new Reservation;
                $reservation->user_id = $userId;
                $reservation->seat_id = $seatId;
                $reservation->save();

                $now = now();
                ReservationStop::query()->insert(array_map(fn (int $cityId): array => [
                    'reservation_id' => $reservation->id,
                    'seat_id' => $seatId,
                    'city_id' => $cityId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $neededStops));

                return true;
            });
        } catch (QueryException) {
            // unique(seat_id, city_id) violation => the seat was taken concurrently
            return false;
        }
    }
}
