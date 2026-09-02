<?php

use App\Http\Controllers\Api\ReservationsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| The consumer API is versioned: every endpoint lives under /api/v1 and is
| named api.v1.*. The unversioned paths below it are kept as deprecated
| aliases so existing clients (and the shipped Postman collection) keep
| working; drop them in the next major release.
|
| Contract: see docs/openapi.yaml.
|
*/

Route::prefix('v1')->name('api.v1.')->middleware('auth:api')->group(function (): void {
    Route::get('user', fn (Request $request) => $request->user())->name('user');

    Route::get('trip/seats', [ReservationsController::class, 'getTripSeats'])
        ->middleware('throttle:seats')
        ->name('trip.seats');

    Route::post('trip/seat/book', [ReservationsController::class, 'bookSeat'])
        ->middleware('throttle:booking')
        ->name('trip.seat.book');
});

/** @deprecated unversioned aliases — use the /api/v1 routes above */
Route::middleware('auth:api')->group(function (): void {
    Route::get('user', fn (Request $request) => $request->user());

    Route::get('trip/seats', [ReservationsController::class, 'getTripSeats'])
        ->middleware('throttle:seats');

    Route::post('trip/seat/book', [ReservationsController::class, 'bookSeat'])
        ->middleware('throttle:booking');
});
