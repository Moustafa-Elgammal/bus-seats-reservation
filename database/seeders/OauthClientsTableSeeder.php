<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class OauthClientsTableSeeder extends Seeder
{
    /**
     * Seed the Passport OAuth clients.
     *
     * Passport 12+ generates UUID client ids and (by default) hashed secrets, so
     * the clients can no longer be inserted with fixed ids/secrets. We create them
     * through the ClientRepository and print the password-grant credentials once so
     * they can be copied into the Postman collection / API consumer configuration.
     */
    public function run(): void
    {
        $clients = app(ClientRepository::class);

        if (Client::query()->count() > 0) {
            $this->command?->warn('OAuth clients already exist; skipping OauthClientsTableSeeder.');

            return;
        }

        $clients->createPersonalAccessGrantClient('Personal Access Client', 'users');

        $passwordClient = $clients->createPasswordGrantClient('Password Grant Client', 'users', confidential: true);

        $this->command?->info('Password grant client created:');
        $this->command?->table(
            ['client_id', 'client_secret'],
            [[$passwordClient->getKey(), $passwordClient->plainSecret]],
        );
    }
}
