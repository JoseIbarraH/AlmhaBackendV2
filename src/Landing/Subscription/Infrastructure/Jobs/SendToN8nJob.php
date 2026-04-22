<?php

declare(strict_types=1);

namespace Src\Landing\Subscription\Infrastructure\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class SendToN8nJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $email;
    private string $token;

    public function __construct(string $email, string $token)
    {
        $this->email = $email;
        $this->token = $token;
    }

    public function handle(): void
    {
        $webhookUrl = config('services.n8n.webhook_url');
        $authToken  = config('services.n8n.auth_token');

        if (!$webhookUrl) {
            Log::error('N8n Webhook URL not configured.');
            return;
        }

        // Build the confirmation URL server-side so n8n's email template only
        // needs {{ $json.body.confirmation_url }} — no hardcoded URLs in the
        // workflow. Uses APP_URL so it works in any environment.
        $confirmationUrl = rtrim((string) config('app.url'), '/')
            . '/api/client/subscribe/confirm?token=' . urlencode($this->token);

        try {
            $response = Http::withHeaders([
                'X-Auth-Token' => $authToken,
                'Accept'       => 'application/json',
            ])->post($webhookUrl, [
                'email'            => $this->email,
                'token'            => $this->token,
                'event'            => 'subscription_created',
                'timestamp'        => now()->toIso8601String(),
                'confirmation_url' => $confirmationUrl,
            ]);

            if ($response->failed()) {
                Log::error('Failed to send data to n8n: ' . $response->body());
                throw new \RuntimeException('N8n webhook failed.');
            }
        } catch (\Exception $e) {
            Log::error('Error sending data to n8n: ' . $e->getMessage());
            throw $e;
        }
    }
}
