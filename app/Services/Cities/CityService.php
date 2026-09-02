<?php

namespace App\Services\Cities;

use App\Models\City;
use App\Services\ErrorService;
use Illuminate\Database\Eloquent\Collection;

class CityService
{
    use ErrorService;

    /**
     * @return Collection<int, City>
     */
    public function getAllCities(): Collection
    {
        return City::all();
    }

    public function create(string $cityName): bool
    {
        $city = new City;
        $city->name = $cityName;

        try {
            if ($city->save()) {
                return true;
            }

            $this->setError(__('City can not be saved'));

            return false;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }
}
