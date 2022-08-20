<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CitiesController extends Controller
{
    public function index(){
        $cities = City::all();
        return view('cities')->with('cities', $cities);
    }

    public function create(Request $request){
        $city = new City();
        $city->name = $request->name;
        $city->save();
        return redirect()->back();
    }
}
