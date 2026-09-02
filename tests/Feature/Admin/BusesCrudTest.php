<?php

namespace Tests\Feature\Admin;

use App\Models\Bus;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusesCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_admin_can_update_an_idle_bus()
    {
        $bus = Bus::factory()->create(['name' => 'Old', 'seats_capacity' => 12]);

        $this->put(route('bus.update', $bus), ['name' => 'New', 'seats_capacity' => 20])
            ->assertSessionHas('success');

        $bus->refresh();
        $this->assertSame('New', $bus->name);
        $this->assertSame(20, $bus->seats_capacity);
    }

    public function test_capacity_of_a_bus_that_runs_trips_can_not_be_changed()
    {
        $bus = Bus::factory()->create(['name' => 'Old', 'seats_capacity' => 12]);
        Trip::factory()->create(['bus_id' => $bus->id]);

        $this->put(route('bus.update', $bus), ['name' => 'New', 'seats_capacity' => 20])
            ->assertSessionHas('service_errors');

        $bus->refresh();
        $this->assertSame(12, $bus->seats_capacity);
        $this->assertSame('Old', $bus->name);
    }

    public function test_a_bus_that_runs_trips_can_still_be_renamed()
    {
        $bus = Bus::factory()->create(['name' => 'Old', 'seats_capacity' => 12]);
        Trip::factory()->create(['bus_id' => $bus->id]);

        $this->put(route('bus.update', $bus), ['name' => 'New', 'seats_capacity' => 12])
            ->assertSessionHas('success');

        $this->assertSame('New', $bus->fresh()->name);
    }

    public function test_admin_can_delete_an_idle_bus()
    {
        $bus = Bus::factory()->create();

        $this->delete(route('bus.destroy', $bus))->assertSessionHas('success');

        $this->assertDatabaseMissing('buses', ['id' => $bus->id]);
    }

    public function test_deleting_a_bus_that_runs_trips_is_refused()
    {
        $trip = Trip::factory()->create();

        $this->delete(route('bus.destroy', $trip->bus))->assertSessionHas('service_errors');

        $this->assertDatabaseHas('buses', ['id' => $trip->bus_id]);
    }
}
