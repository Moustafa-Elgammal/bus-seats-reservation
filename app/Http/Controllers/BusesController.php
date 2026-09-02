<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBusRequest;
use App\Models\Bus;
use Illuminate\Http\RedirectResponse;

class BusesController extends Controller
{
    public function index()
    {
        $buses = Bus::all();

        return view('buses')->with('buses', $buses);
    }

    public function create(CreateBusRequest $request): RedirectResponse
    {
        Bus::create($request->validated());

        return redirect()->back();
    }
}
