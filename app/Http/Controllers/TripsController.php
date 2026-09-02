<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddTripStationRequest;
use App\Http\Requests\CreateTripRequest;
use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripStation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TripsController extends Controller
{
    public function index()
    {
        $trips = Trip::all();
        $buses = Bus::all();
        $cities = City::all();

        return view('trips')
            ->with('trips', $trips)
            ->with('cities', $cities)
            ->with('buses', $buses);
    }

    public function create(CreateTripRequest $request): RedirectResponse
    {
        // wrap the insert + TripObserver seat generation so they commit together
        DB::transaction(fn () => Trip::query()->create($request->validated()));

        return redirect()->back();
    }

    public function addStation(int $id, AddTripStationRequest $request): RedirectResponse
    {
        $nextOrder = (int) TripStation::query()->where('trip_id', $id)->max('station_order') + 1;

        TripStation::create([
            'trip_id' => $id,
            'city_id' => $request->integer('city_id'),
            'station_order' => $nextOrder,
        ]);

        return redirect()->back();
    }
}
