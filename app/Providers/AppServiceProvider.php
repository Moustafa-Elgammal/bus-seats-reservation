<?php

namespace App\Providers;

use App\Services\Reservations\Interfaces\ReservationInterface;
use App\Services\Reservations\ReservationService;
use App\Services\Seats\Interfaces\TripSeatServiceInterface;
use App\Services\Seats\TripSeatService;
use App\Services\Trips\Interfaces\TripServiceInterface;
use App\Services\Trips\TripService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TripServiceInterface::class, TripService::class);
        $this->app->bind(TripSeatServiceInterface::class, TripSeatService::class);
        $this->app->bind(ReservationInterface::class, ReservationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Register the named rate limiters used by the consumer API.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('booking', fn (Request $request): Limit => Limit::perMinute(
            (int) config('reservations.booking_rate_limit')
        )->by($request->user()?->getAuthIdentifier() ?: $request->ip()));

        RateLimiter::for('seats', fn (Request $request): Limit => Limit::perMinute(
            (int) config('reservations.seats_rate_limit')
        )->by($request->user()?->getAuthIdentifier() ?: $request->ip()));
    }
}
