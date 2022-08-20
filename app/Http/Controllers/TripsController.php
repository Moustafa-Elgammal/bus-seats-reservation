<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\City;
use App\Models\Trip;
use App\Models\TripsStation;
use Illuminate\Http\Request;

class TripsController extends Controller
{
    public function index(){
        $trips = Trip::all();
        $buses = Bus::all();
        $cities = City::all();
        return view('trips')
            ->with('trips', $trips)
            ->with('cities', $cities)
            ->with('buses', $buses);
    }

    public function create(Request $request){
        $trip = new Trip();

        $trip->name = $request->name;
        $trip->bus_id = $request->bus_id;

        $trip->save();
        return redirect()->back();
    }

    public function addStation($id, Request $request){
     $station = new TripsStation();

     $station->trip_id = $id;

     $station->city_id = $request->city_id;

     $station->station_order = $request->last_order + 1;

     $station->save();

    return redirect()->back();
    }
}
