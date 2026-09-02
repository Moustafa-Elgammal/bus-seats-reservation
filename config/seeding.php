<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin User
    |--------------------------------------------------------------------------
    |
    | Credentials for the admin account created by DatabaseSeeder. The defaults
    | are meant for local development only — override them through the
    | environment for any shared, staging, or production environment.
    |
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'admin'),
        'email' => env('ADMIN_EMAIL', 'admin@admin.com'),
        'password' => env('ADMIN_PASSWORD', '123456'),
    ],

];
