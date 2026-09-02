<?php

namespace Tests\Feature\Api;

use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripStation;
use App\Models\User;
use App\Services\Reservations\Interfaces\ReservationInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ReservationsApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Passport::actingAs($this->user, ['*'], 'api');
    }

    /**
     * @param  list<string>  $cityNames
     * @return array{0: Trip, 1: Collection<string, City>}
     */
    private function makeTrip(array $cityNames, int $capacity = 12): array
    {
        $cities = collect($cityNames)->mapWithKeys(
            fn (string $name) => [$name => City::factory()->create(['name' => $name])]
        );

        $bus = Bus::factory()->create(['seats_capacity' => $capacity]);
        $trip = Trip::factory()->create(['bus_id' => $bus->id]);

        $cities->values()->each(fn (City $city, int $order) => TripStation::factory()->create([
            'trip_id' => $trip->id,
            'city_id' => $city->id,
            'station_order' => $order,
        ]));

        return [$trip, $cities];
    }

    private function assertEnvelope(TestResponse $response, bool $okay): void
    {
        $this->assertSame(['data', 'message', 'errors', 'okay'], array_keys($response->json()));
        $this->assertSame($okay, $response->json('okay'));
    }

    public function test_get_trip_seats_returns_envelope_with_seat_ids()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut'], capacity: 4);

        $response = $this->getJson("/api/v1/trip/seats?trip_id={$trip->id}&from_city_id={$c['Cairo']->id}&to_city_id={$c['Asyut']->id}");

        $response->assertOk();
        $this->assertEnvelope($response, true);
        $this->assertCount(4, $response->json('data'));
        $this->assertSame([], $response->json('errors'));
    }

    public function test_get_trip_seats_invalid_route_returns_422_envelope()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);

        $response = $this->getJson("/api/v1/trip/seats?trip_id={$trip->id}&from_city_id={$c['Cairo']->id}&to_city_id={$c['Cairo']->id}");

        $response->assertStatus(422);
        $this->assertEnvelope($response, false);
        $this->assertArrayHasKey('to_city_id', $response->json('errors'));
    }

    public function test_get_trip_seats_valid_route_with_no_free_seats_returns_200_empty_data()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut'], capacity: 3);

        $service = app(ReservationInterface::class);
        foreach ($trip->seats as $seat) {
            $service->bookSeat($trip->id, $seat->id, $c['Cairo']->id, $c['Asyut']->id, $this->user->id);
        }

        $response = $this->getJson("/api/v1/trip/seats?trip_id={$trip->id}&from_city_id={$c['Cairo']->id}&to_city_id={$c['Asyut']->id}");

        $response->assertOk();
        $this->assertEnvelope($response, true);
        $this->assertSame([], $response->json('data'));
    }

    public function test_book_seat_success_returns_200_envelope()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);
        $seatId = $trip->seats->first()->id;

        $response = $this->postJson('/api/v1/trip/seat/book', [
            'trip_id' => $trip->id,
            'seat_id' => $seatId,
            'from_city_id' => $c['Cairo']->id,
            'to_city_id' => $c['Asyut']->id,
        ]);

        $response->assertOk();
        $this->assertEnvelope($response, true);
        $this->assertDatabaseHas('customers_seats_reservations', [
            'seat_id' => $seatId,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_book_seat_unavailable_returns_422_envelope()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);
        $seatId = $trip->seats->first()->id;
        $payload = [
            'trip_id' => $trip->id,
            'seat_id' => $seatId,
            'from_city_id' => $c['Cairo']->id,
            'to_city_id' => $c['Asyut']->id,
        ];

        $this->postJson('/api/v1/trip/seat/book', $payload)->assertOk();

        $response = $this->postJson('/api/v1/trip/seat/book', $payload);
        $response->assertStatus(422);
        $this->assertEnvelope($response, false);
        $this->assertSame([], $response->json('data'));
    }

    public function test_book_seat_for_a_seat_not_on_the_trip_returns_422()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);
        [$otherTrip] = $this->makeTrip(['Tanta', 'Banha']);
        $foreignSeatId = $otherTrip->seats->first()->id;

        $response = $this->postJson('/api/v1/trip/seat/book', [
            'trip_id' => $trip->id,
            'seat_id' => $foreignSeatId,
            'from_city_id' => $c['Cairo']->id,
            'to_city_id' => $c['Asyut']->id,
        ]);

        $response->assertStatus(422);
        $this->assertEnvelope($response, false);
        $this->assertArrayHasKey('seat_id', $response->json('errors'));
    }

    public function test_versioned_routes_are_named()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut']);

        $this->getJson(route('api.v1.trip.seats', [
            'trip_id' => $trip->id,
            'from_city_id' => $c['Cairo']->id,
            'to_city_id' => $c['Asyut']->id,
        ]))->assertOk();
    }

    public function test_deprecated_unversioned_aliases_still_resolve()
    {
        [$trip, $c] = $this->makeTrip(['Cairo', 'AlMinya', 'Asyut'], capacity: 2);

        $response = $this->getJson("/api/trip/seats?trip_id={$trip->id}&from_city_id={$c['Cairo']->id}&to_city_id={$c['Asyut']->id}");

        $response->assertOk();
        $this->assertEnvelope($response, true);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_validation_error_returns_envelope_shape()
    {
        $response = $this->postJson('/api/v1/trip/seat/book', []);

        $response->assertStatus(422);
        $this->assertEnvelope($response, false);
        $this->assertSame([], $response->json('data'));
        $this->assertNotEmpty($response->json('errors'));
    }
}
