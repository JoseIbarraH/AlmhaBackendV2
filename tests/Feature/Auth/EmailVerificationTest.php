<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Support\Str;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    public function test_verifies_email_with_valid_token(): void
    {
        $token = Str::random(64);
        $this->createUser([
            'email_verified_at' => null,
            'verification_token' => $token,
        ]);

        $response = $this->getJson("/api/v1/auth/email/verify/{$token}");

        $response->assertStatus(200);
    }

    public function test_returns_404_on_invalid_token(): void
    {
        $response = $this->getJson('/api/v1/auth/email/verify/nonexistent-token');

        $response->assertStatus(404);
    }

    public function test_resend_returns_422_when_email_missing(): void
    {
        $response = $this->postJson('/api/v1/auth/email/resend', []);

        $response->assertStatus(422);
    }

    public function test_resend_returns_429_after_too_many_attempts(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/auth/email/resend', [
                'email' => 'someone@example.com',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/email/resend', [
            'email' => 'someone@example.com',
        ]);

        $response->assertStatus(429);
    }
}
