<?php

namespace App\Http\Requests;

use App\Http\Concerns\ApiResponses;
use App\Services\Seats\Interfaces\TripSeatServiceInterface;
use App\Services\Trips\Interfaces\TripServiceInterface;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class BookSeatRequest extends FormRequest
{
    use ApiResponses;

    /**
     * Authentication is enforced by the route's `auth:api` middleware; checking
     * Auth::check() here would silently consult the default (web) guard.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'trip_id' => 'required|exists:trips,id',
            'seat_id' => 'required|exists:trips_seats,id',
            'from_city_id' => 'required|exists:cities,id',
            'to_city_id' => 'required|exists:cities,id',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $tripId = (int) $this->trip_id;

            if (! app(TripSeatServiceInterface::class)->checkSeatBelongsToTrip((int) $this->seat_id, $tripId)) {
                $validator->errors()->add('seat_id', __('This seat does not belong to the given trip.'));
            }

            $validRoute = app(TripServiceInterface::class)->validateNeededTripRoute(
                $tripId,
                (int) $this->from_city_id,
                (int) $this->to_city_id,
            );

            if (! $validRoute) {
                $validator->errors()->add('to_city_id', __(
                    'from_city_id and to_city_id must be two different stops on the trip route, in travel order.'
                ));
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->apiFail(__('reservation data validation errors'), (new ValidationException($validator))->errors())
        );
    }
}
