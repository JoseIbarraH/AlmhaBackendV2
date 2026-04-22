<?php

declare(strict_types=1);

namespace Tests\Feature\Landing;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ContactTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.n8n.contact_webhook_url' => 'https://example.test/webhook/contact']);
    }

    public function test_valid_submission_returns_200_and_forwards_to_webhook(): void
    {
        Http::fake([
            'example.test/*' => Http::response(['ok' => true], 200),
        ]);

        $response = $this->postJson('/api/v1/contact', [
            'name'        => 'Jane Doe',
            'email'       => 'jane@example.com',
            'countryCode' => '+57',
            'phone'       => '300 123 4567',
            'procedure'   => 'Rinoplastia',
            'message'     => 'Quisiera más información',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        Http::assertSent(fn ($req) => str_contains($req->url(), 'example.test/webhook/contact'));
    }

    public function test_missing_required_fields_returns_422(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone', 'message']);
    }

    public function test_phone_with_invalid_characters_rejected(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'name'    => 'Jane',
            'email'   => 'jane@example.com',
            'phone'   => 'abc-def',
            'message' => 'Hola quisiera información',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_missing_webhook_config_returns_503(): void
    {
        config(['services.n8n.contact_webhook_url' => null]);

        $response = $this->postJson('/api/v1/contact', [
            'name'    => 'Jane Doe',
            'email'   => 'jane@example.com',
            'phone'   => '3001234567',
            'message' => 'Hola quisiera información',
        ]);

        $response->assertStatus(503)
            ->assertJson(['error' => 'misconfigured']);
    }

    public function test_webhook_failure_returns_502(): void
    {
        Http::fake([
            'example.test/*' => Http::response(['error' => 'boom'], 500),
        ]);

        $response = $this->postJson('/api/v1/contact', [
            'name'    => 'Jane Doe',
            'email'   => 'jane@example.com',
            'phone'   => '3001234567',
            'message' => 'Hola quisiera información',
        ]);

        $response->assertStatus(502)
            ->assertJson(['error' => 'forward_failed']);
    }
}
