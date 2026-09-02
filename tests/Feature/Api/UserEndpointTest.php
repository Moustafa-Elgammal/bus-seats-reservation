<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_endpoint_rejects_guests()
    {
        $this->getJson('/api/v1/user')->assertStatus(401);
    }

    public function test_user_endpoint_returns_the_authenticated_user_via_passport()
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*'], 'api');

        $this->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }
}
