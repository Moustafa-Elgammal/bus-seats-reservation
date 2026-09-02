<?php

use App\Http\Controllers\BusesController;
use App\Http\Controllers\CitiesController;
use App\Http\Controllers\TripsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('cities', [CitiesController::class, 'index'])->name('cities');
    Route::post('city', [CitiesController::class, 'create'])->name('city.create');
    Route::put('city/{city}', [CitiesController::class, 'update'])->name('city.update');
    Route::delete('city/{city}', [CitiesController::class, 'destroy'])->name('city.destroy');

    Route::get('buses', [BusesController::class, 'index'])->name('buses');
    Route::post('bus', [BusesController::class, 'create'])->name('bus.create');
    Route::put('bus/{bus}', [BusesController::class, 'update'])->name('bus.update');
    Route::delete('bus/{bus}', [BusesController::class, 'destroy'])->name('bus.destroy');

    Route::get('trips', [TripsController::class, 'index'])->name('trips');
    Route::post('trip', [TripsController::class, 'create'])->name('trip.create');
    Route::put('trip/{trip}', [TripsController::class, 'update'])->name('trip.update');
    Route::delete('trip/{trip}', [TripsController::class, 'destroy'])->name('trip.destroy');
    Route::post('trip/station/{id}', [TripsController::class, 'addStation'])->name('trip.station.add');
    Route::delete('trip/station/{station}', [TripsController::class, 'removeStation'])->name('trip.station.destroy');
});

require __DIR__.'/auth.php';
