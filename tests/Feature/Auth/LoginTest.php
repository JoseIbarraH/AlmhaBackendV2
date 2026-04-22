<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;

class LoginTest extends TestCase
{
    private const LOGIN_ENDPOINT = '/api/v1/auth/login';

    public function test_returns_token_on_valid_credentials(): void
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in'])
            ->assertJsonPath('token_type', 'bearer');
    }

    public function test_returns_401_on_wrong_password(): void
    {
        $this->createUser([
            'email' => 'test@example.com',
            'password' => bcrypt('correctpassword'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error', 'invalid_credentials');
    }

    public function test_returns_401_on_nonexistent_user(): void
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'nobody@example.com',
            'password' => 'anypassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_returns_403_on_unverified_email(): void
    {
        $this->createUser([
            'email' => 'unverified@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'unverified@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('error', 'not_verified');
    }

    public function test_returns_422_when_email_is_missing(): void
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'validation_error')
            ->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_returns_422_when_password_is_missing(): void
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'validation_error')
            ->assertJsonStructure(['errors' => ['password']]);
    }

    public function test_returns_429_after_too_many_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(self::LOGIN_ENDPOINT, [
                'email' => 'brute@example.com',
                'password' => 'wrong',
            ]);
        }

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'brute@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(429)
            ->assertJsonPath('error', 'too_many_attempts');
    }
}
