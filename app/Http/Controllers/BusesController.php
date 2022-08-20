<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\City;
use Illuminate\Http\Request;

class BusesController extends Controller
{
    public function index(){
        $buses = Bus::all();
        return view('buses')->with('buses', $buses);
    }

    public function create(Request $request){
        $bus = new Bus();
        $bus->name = $request->name;
        $bus->seats_capacity = $request->seats_capacity;
        $bus->save();
        return redirect()->back();
    }
}
