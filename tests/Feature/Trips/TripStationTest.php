<?php

namespace Tests\Feature\Trips;

use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripStation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripStationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_add_station_computes_order_server_side_and_ignores_client_input()
    {
        $trip = Trip::factory()->create();
        foreach ([3, 7] as $order) {
            TripStation::factory()->create([
                'trip_id' => $trip->id,
                'city_id' => City::factory()->create()->id,
                'station_order' => $order,
            ]);
        }
        $newCity = City::factory()->create();

        $this->post("/trip/station/{$trip->id}", [
            'city_id' => $newCity->id,
            'last_order' => 0, // attacker-supplied, must be ignored
        ])->assertRedirect();

        $this->assertDatabaseHas('trips_stations', [
            'trip_id' => $trip->id,
            'city_id' => $newCity->id,
            'station_order' => 8,
        ]);
    }

    public function test_add_first_station_gets_order_one()
    {
        $trip = Trip::factory()->create();
        $city = City::factory()->create();

        $this->post("/trip/station/{$trip->id}", ['city_id' => $city->id]);

        $this->assertDatabaseHas('trips_stations', [
            'trip_id' => $trip->id,
            'city_id' => $city->id,
            'station_order' => 1,
        ]);
    }

    public function test_add_station_rejects_a_city_already_on_the_route()
    {
        $trip = Trip::factory()->create();
        $city = City::factory()->create();
        TripStation::factory()->create([
            'trip_id' => $trip->id,
            'city_id' => $city->id,
            'station_order' => 1,
        ]);

        $this->post("/trip/station/{$trip->id}", ['city_id' => $city->id])
            ->assertSessionHasErrors('city_id');

        $this->assertDatabaseCount('trips_stations', 1);
    }

    public function test_add_station_rejects_an_unknown_city()
    {
        $trip = Trip::factory()->create();

        $this->post("/trip/station/{$trip->id}", ['city_id' => 999999])
            ->assertSessionHasErrors('city_id');

        $this->assertDatabaseCount('trips_stations', 0);
    }

    public function test_create_trip_requires_an_existing_bus()
    {
        Bus::factory()->create();

        $this->post('/trip', ['name' => 'Ghost Trip', 'bus_id' => 999999])
            ->assertSessionHasErrors('bus_id');

        $this->assertDatabaseMissing('trips', ['name' => 'Ghost Trip']);
    }
}
