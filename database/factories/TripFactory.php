<?php

namespace Database\Factories;

use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripStation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name.' Trip',
            'bus_id' => Bus::factory(),
        ];
    }

    /**
     * Run the trip on a bus of the given capacity — TripObserver turns that
     * into the same number of seats.
     */
    public function capacity(int $seats): static
    {
        return $this->state(['bus_id' => Bus::factory()->state(['seats_capacity' => $seats])]);
    }

    /**
     * Attach an ordered route. Entries are City models, or names of cities to
     * create on the fly.
     *
     * @param  list<City|string>  $cities
     */
    public function withRoute(array $cities): static
    {
        return $this->afterCreating(function (Trip $trip) use ($cities): void {
            foreach (array_values($cities) as $index => $city) {
                $city = $city instanceof City ? $city : City::factory()->create(['name' => $city]);

                TripStation::create([
                    'trip_id' => $trip->id,
                    'city_id' => $city->id,
                    'station_order' => $index + 1,
                ]);
            }
        });
    }
}
