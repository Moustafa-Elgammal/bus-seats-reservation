<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OauthClientsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('oauth_clients')->delete();

        \DB::table('oauth_clients')->insert(array (
            0 =>
            array (
                'created_at' => '2022-08-19 23:00:21',
                'id' => 1,
                'name' => 'Laravel Personal Access Client',
                'password_client' => 0,
                'personal_access_client' => 1,
                'provider' => NULL,
                'redirect' => 'http://localhost',
                'revoked' => 0,
                'secret' => '1TITUtX6fJ18ggl9pMSDU1m9n4yu5pUkEh2BFadl',
                'updated_at' => '2022-08-19 23:00:21',
                'user_id' => NULL,
            ),
            1 =>
            array (
                'created_at' => '2022-08-19 23:00:21',
                'id' => 2,
                'name' => 'Laravel Password Grant Client',
                'password_client' => 1,
                'personal_access_client' => 0,
                'provider' => 'users',
                'redirect' => 'http://localhost',
                'revoked' => 0,
                'secret' => 'gmQXxhah3ZKUaqIzAx9WAGau3E06FrH9qvKnhwD0',
                'updated_at' => '2022-08-19 23:00:21',
                'user_id' => NULL,
            )
        ));


    }
}
