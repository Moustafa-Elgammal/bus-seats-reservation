<?php

namespace App\Providers;

use App\Services\Reservations\Interfaces\ReservationInterface;
use App\Services\Reservations\ReservationService;
use App\Services\Trips\Interfaces\TripServiceInterface;
use App\Services\Trips\TripService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TripServiceInterface::class, TripService::class);
        $this->app->bind(ReservationInterface::class, ReservationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
