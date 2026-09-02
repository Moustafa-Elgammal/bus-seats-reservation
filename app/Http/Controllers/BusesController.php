<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBusRequest;
use App\Http\Requests\UpdateBusRequest;
use App\Models\Bus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BusesController extends Controller
{
    public function index(): View
    {
        $buses = Bus::all();

        return view('buses')->with('buses', $buses);
    }

    public function create(CreateBusRequest $request): RedirectResponse
    {
        Bus::create($request->validated());

        return redirect()->back();
    }

    /**
     * Seats are generated per trip from the capacity at creation time, so
     * changing the capacity of a bus that already runs trips would leave those
     * trips with a stale number of seats.
     */
    public function update(UpdateBusRequest $request, Bus $bus): RedirectResponse
    {
        $data = $request->validated();

        if ((int) $data['seats_capacity'] !== $bus->seats_capacity && $bus->trips()->exists()) {
            return redirect()->back()->with('service_errors', [
                __('The capacity of a bus that already runs trips can not be changed.'),
            ]);
        }

        $bus->update($data);

        return redirect()->back()->with('success', __('bus updated'));
    }

    public function destroy(Bus $bus): RedirectResponse
    {
        if ($bus->trips()->exists()) {
            return redirect()->back()->with('service_errors', [
                __('This bus still runs trips and can not be deleted.'),
            ]);
        }

        $bus->delete();

        return redirect()->back()->with('success', __('bus deleted'));
    }
}
