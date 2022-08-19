<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitiesSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\City::factory()->create(['name' => 'Cairo']);
        \App\Models\City::factory()->create(['name' => 'Giza']);
        \App\Models\City::factory()->create(['name' => 'AlFayyum']);
        \App\Models\City::factory()->create(['name' => 'AlMinya']);
        \App\Models\City::factory()->create(['name' => 'Asyut']);
    }
}
