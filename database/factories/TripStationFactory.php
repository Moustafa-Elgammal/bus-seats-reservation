<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Trip;
use App\Models\TripStation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TripStation>
 */
class TripStationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Prefer Trip::factory()->withRoute([...]) for a whole route; this factory
     * is for a single stop with explicit attributes.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'city_id' => City::factory(),
            'station_order' => 1,
        ];
    }
}
