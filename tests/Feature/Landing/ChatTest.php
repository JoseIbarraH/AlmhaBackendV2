<?php

declare(strict_types=1);

namespace Tests\Feature\Landing;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ChatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.n8n.chat_webhook_url' => 'https://example.test/webhook/chat']);
    }

    public function test_valid_message_is_forwarded_and_reply_returned(): void
    {
        Http::fake([
            'example.test/*' => Http::response(['output' => 'Hola, soy el asistente'], 200),
        ]);

        $response = $this->postJson('/api/v1/chat', [
            'chatInput' => '¿Cuáles son los precios?',
            'sessionId' => 'session-abc-123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['output' => 'Hola, soy el asistente']);

        Http::assertSent(fn ($req) => str_contains($req->url(), 'example.test/webhook/chat')
            && $req['chatInput'] === '¿Cuáles son los precios?'
            && $req['sessionId'] === 'session-abc-123');
    }

    public function test_empty_chatInput_rejected(): void
    {
        $response = $this->postJson('/api/v1/chat', [
            'chatInput' => '',
            'sessionId' => 'session-abc-123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['chatInput']);
    }

    public function test_invalid_sessionId_format_rejected(): void
    {
        $response = $this->postJson('/api/v1/chat', [
            'chatInput' => 'hola',
            'sessionId' => 'bad session!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sessionId']);
    }

    public function test_webhook_failure_returns_502(): void
    {
        Http::fake([
            'example.test/*' => Http::response(null, 500),
        ]);

        $response = $this->postJson('/api/v1/chat', [
            'chatInput' => 'hola',
            'sessionId' => 'session-xyz-999',
        ]);

        $response->assertStatus(502);
    }
}
