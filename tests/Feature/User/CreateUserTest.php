<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use Tests\TestCase;

class CreateUserTest extends TestCase
{
    private const ENDPOINT = '/api/v1/users';

    public function test_returns_201_on_valid_creation(): void
    {
        $response = $this->actingAsAdmin()->postJson(self::ENDPOINT, [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'nuevo@example.com']);
    }

    public function test_returns_401_when_unauthenticated(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    public function test_returns_403_when_missing_permission(): void
    {
        $response = $this->actingAsUserWithRole('blog_manager')->postJson(self::ENDPOINT, [
            'name' => 'Test',
            'email' => 'test2@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_returns_422_on_invalid_email(): void
    {
        $response = $this->actingAsAdmin()->postJson(self::ENDPOINT, [
            'name' => 'Test',
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'validation_error')
            ->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_returns_422_on_duplicate_email(): void
    {
        $this->createUser(['email' => 'existing@example.com']);

        $response = $this->actingAsAdmin()->postJson(self::ENDPOINT, [
            'name' => 'Test',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }
}
