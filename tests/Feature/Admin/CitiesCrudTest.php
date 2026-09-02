<?php

namespace Tests\Feature\Admin;

use App\Models\City;
use App\Models\Trip;
use App\Models\TripStation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitiesCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_admin_can_rename_a_city()
    {
        $city = City::factory()->create(['name' => 'Cairo']);

        $this->from(route('cities'))
            ->put(route('city.update', $city), ['name' => 'Al Qahirah'])
            ->assertRedirect(route('cities'))
            ->assertSessionHas('success');

        $this->assertSame('Al Qahirah', $city->fresh()->name);
    }

    public function test_renaming_to_an_existing_city_name_is_rejected()
    {
        City::factory()->create(['name' => 'Giza']);
        $city = City::factory()->create(['name' => 'Cairo']);

        $this->from(route('cities'))
            ->put(route('city.update', $city), ['name' => 'Giza'])
            ->assertSessionHasErrors('name');

        $this->assertSame('Cairo', $city->fresh()->name);
    }

    public function test_keeping_its_own_name_is_not_a_duplicate()
    {
        $city = City::factory()->create(['name' => 'Cairo']);

        $this->put(route('city.update', $city), ['name' => 'Cairo'])
            ->assertSessionHasNoErrors();
    }

    public function test_admin_can_delete_an_unused_city()
    {
        $city = City::factory()->create();

        $this->delete(route('city.destroy', $city))->assertSessionHas('success');

        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
    }

    public function test_deleting_a_city_that_is_on_a_route_is_refused()
    {
        $trip = Trip::factory()->create();
        $city = City::factory()->create();
        TripStation::create(['trip_id' => $trip->id, 'city_id' => $city->id, 'station_order' => 1]);

        $this->delete(route('city.destroy', $city))->assertSessionHas('service_errors');

        $this->assertDatabaseHas('cities', ['id' => $city->id]);
    }
}
