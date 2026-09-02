<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddTripStationRequest;
use App\Http\Requests\CreateTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripStation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TripsController extends Controller
{
    public function index(): View
    {
        $trips = Trip::with('stations.city')->get();
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

    /**
     * Only the label is editable: the bus determines how many seats were
     * generated for the trip, and those seats may already be sold.
     */
    public function update(UpdateTripRequest $request, Trip $trip): RedirectResponse
    {
        $trip->update($request->validated());

        return redirect()->back()->with('success', __('trip updated'));
    }

    public function destroy(Trip $trip): RedirectResponse
    {
        if ($this->hasReservations($trip)) {
            return redirect()->back()->with('service_errors', [
                __('This trip has reservations and can not be deleted.'),
            ]);
        }

        // stations and seats go with it (cascadeOnDelete)
        $trip->delete();

        return redirect()->back()->with('success', __('trip deleted'));
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

    /**
     * Removing a stop rewrites the legs every sold seat was priced against, so
     * it is only allowed while the trip has no reservations.
     */
    public function removeStation(TripStation $station): RedirectResponse
    {
        $trip = Trip::query()->findOrFail($station->trip_id);

        if ($this->hasReservations($trip)) {
            return redirect()->back()->with('service_errors', [
                __('This trip has reservations; its route can not be changed.'),
            ]);
        }

        $station->delete();

        return redirect()->back()->with('success', __('station removed'));
    }

    private function hasReservations(Trip $trip): bool
    {
        return $trip->seats()->whereHas('reservations')->exists();
    }
}
