<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCityRequest;
use App\Models\City;
use App\Services\Cities\CityService;

class CitiesController extends Controller
{

    public function __construct(protected CityService $cityService){}

    public function index(){
        $cities = City::all();
        return view('cities')->with('cities', $cities);
    }


    /** add new city
     * @param CreateCityRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(CreateCityRequest $request){

        if($this->cityService->create($request->name)){
            return redirect()->back()->with('success', __("new city created"));
        }

        return redirect()->back()->with("service_errors", $this->cityService->getErrors());
    }
}
