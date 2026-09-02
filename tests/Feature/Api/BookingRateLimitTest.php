<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class BookingRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_endpoint_is_rate_limited_per_user()
    {
        config(['reservations.booking_rate_limit' => 3]);

        Passport::actingAs(User::factory()->create(), ['*'], 'api');

        // Requests still count toward the limiter even when validation rejects them.
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/trip/seat/book', [])
                ->assertStatus(422)
                ->assertJsonPath('okay', false);
        }

        $this->postJson('/api/v1/trip/seat/book', [])->assertStatus(429);
    }

    public function test_seats_endpoint_uses_a_separate_limiter()
    {
        config([
            'reservations.booking_rate_limit' => 1,
            'reservations.seats_rate_limit' => 5,
        ]);

        Passport::actingAs(User::factory()->create(), ['*'], 'api');

        // Exhaust the booking limiter.
        $this->postJson('/api/v1/trip/seat/book', [])->assertStatus(422);
        $this->postJson('/api/v1/trip/seat/book', [])->assertStatus(429);

        // The seats limiter is independent and still lets requests through.
        $this->getJson('/api/v1/trip/seats')->assertStatus(422);
    }
}
