<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Models\City;
use App\Services\Cities\CityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CitiesController extends Controller
{
    public function __construct(protected CityService $cityService) {}

    public function index(): View
    {
        return view('cities')->with('cities', $this->cityService->getAllCities());
    }

    public function create(CreateCityRequest $request): RedirectResponse
    {
        if ($this->cityService->create($request->string('name')->toString())) {
            return redirect()->back()->with('success', __('new city created'));
        }

        return redirect()->back()->with('service_errors', $this->cityService->getErrors());
    }

    public function update(UpdateCityRequest $request, City $city): RedirectResponse
    {
        if ($this->cityService->rename($city, $request->string('name')->toString())) {
            return redirect()->back()->with('success', __('city updated'));
        }

        return redirect()->back()->with('service_errors', $this->cityService->getErrors());
    }

    public function destroy(City $city): RedirectResponse
    {
        if ($this->cityService->delete($city)) {
            return redirect()->back()->with('success', __('city deleted'));
        }

        return redirect()->back()->with('service_errors', $this->cityService->getErrors());
    }
}
