<?php

namespace App\Services\Cities;

use App\Models\City;
use App\Services\ErrorService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;

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

    public function rename(City $city, string $cityName): bool
    {
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

    /**
     * A city that is still a stop on a route, or part of a sold leg, is kept:
     * the schema restricts the delete and the error is reported to the admin.
     */
    public function delete(City $city): bool
    {
        try {
            return (bool) $city->delete();
        } catch (QueryException) {
            $this->setError(__('This city is used by a trip route or a reservation and can not be deleted.'));

            return false;
        }
    }
}
