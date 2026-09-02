<?php

namespace Tests\Feature\Seeders;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_is_seeded_from_configured_credentials()
    {
        config([
            'seeding.admin.name' => 'Root',
            'seeding.admin.email' => 'root@example.test',
            'seeding.admin.password' => 's3cret-pass',
        ]);

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'root@example.test')->first();

        $this->assertNotNull($admin);
        $this->assertSame('Root', $admin->name);
        $this->assertSame(1, (int) $admin->user_group);
        $this->assertTrue(Hash::check('s3cret-pass', $admin->password));
        $this->assertDatabaseMissing('users', ['email' => 'admin@admin.com']);
    }
}
