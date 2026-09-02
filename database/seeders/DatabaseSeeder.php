<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Note: model events are intentionally NOT suppressed here so that
     * TripObserver generates the trip seats when TripSeed creates a trip.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => '123456',
            'user_group' => 1,
        ]);

        $this->call(TripSeed::class);
        $this->call(OauthClientsTableSeeder::class);
    }
}
