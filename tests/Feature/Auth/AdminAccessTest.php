<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_gate_and_is_admin_are_true_for_admin_user()
    {
        $user = User::factory()->admin()->create();

        $this->assertTrue($user->isAdmin());
        $this->assertTrue(Gate::forUser($user)->allows('admin'));
    }

    public function test_admin_gate_and_is_admin_are_false_for_regular_user()
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isAdmin());
        $this->assertFalse(Gate::forUser($user)->allows('admin'));
        $this->assertFalse((new User)->isAdmin());
    }

    public function test_non_admin_hitting_admin_route_gets_403()
    {
        $this->actingAs(User::factory()->create())
            ->get('/cities')
            ->assertForbidden();
    }

    public function test_admin_hitting_admin_route_succeeds()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/cities')
            ->assertOk();
    }

    public function test_guest_hitting_admin_route_is_redirected_to_login()
    {
        $this->get('/cities')->assertRedirect('/login');
    }
}
