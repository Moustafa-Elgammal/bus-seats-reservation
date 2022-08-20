<?php

namespace Tests\Feature\Cities;

use App\Models\City;
use App\Services\Cities\CityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CitiesServiceTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_all_cities()
    {
        City::factory(6)->create();

        $toBeChecked = ['id','name'];
        $db_cities = City::all()->pluck($toBeChecked);
        $service_cities = (new CityService())->getAllCities()->pluck($toBeChecked);
        $this->assertEquals($service_cities, $db_cities);
    }

    /** test create city
     * @return void
     */
    public function test_create_city()
    {
        $this->assertTrue((new CityService())->create($this->faker->city()));
    }
}
