<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AddTripStationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // the route group already applies the `admin` middleware
        return Auth::check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'city_id' => [
                'required', 'integer', 'exists:cities,id',
                Rule::unique('trips_stations', 'city_id')->where('trip_id', (int) $this->route('id')),
            ],
        ];
    }
}
