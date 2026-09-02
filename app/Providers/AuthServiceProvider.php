<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        // The API consumers (see the Postman collection) authenticate with the
        // OAuth password grant, which Passport 12+ no longer enables by default.
        Passport::enablePasswordGrant();
    }
}
