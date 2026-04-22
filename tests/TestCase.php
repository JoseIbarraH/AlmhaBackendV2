<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Queue;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->seedPermissions();
    }

    protected function seedPermissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function createAdminUser(): User
    {
        $user = $this->createUser(['email_verified_at' => now()]);
        $user->assignRole('super_admin');

        return $user;
    }

    protected function actingAsAdmin(): static
    {
        $user = $this->createAdminUser();
        $token = JWTAuth::fromUser($user);

        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }

    protected function actingAsUserWithRole(string $role): static
    {
        $user = $this->createUser(['email_verified_at' => now()]);
        $user->assignRole($role);
        $token = JWTAuth::fromUser($user);

        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }
}
