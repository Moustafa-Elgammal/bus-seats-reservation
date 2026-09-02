<?php

namespace App\Services\Cities;

use App\Models\City;
use App\Services\ErrorService;
use Illuminate\Database\Eloquent\Collection;

class CityService
{
    use ErrorService;

    /**
     * @return Collection
     */
    public function getAllCities()
    {
        return City::all();
    }

    /** create city
     */
    public function create(string $city_name): bool
    {
        $city = new City;
        $city->name = $city_name;

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
