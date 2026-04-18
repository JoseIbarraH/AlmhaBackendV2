<?php

declare(strict_types=1);

namespace Src\Admin\User\Infrastructure\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class SendUserVerificationToN8nJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $name;
    private string $email;
    private string $token;

    public function __construct(string $name, string $email, string $token)
    {
        $this->name = $name;
        $this->email = $email;
        $this->token = $token;
    }

    public function handle(): void
    {
        $webhookUrl = config('services.n8n.user_verification_webhook_url') ?? config('services.n8n.webhook_url');
        $authToken = config('services.n8n.auth_token');

        if (!$webhookUrl) {
            Log::error('N8n Webhook URL not configured correctly (User Verification).');
            return;
        }

        try {
            $frontendUrl = config('app.frontend_url');
            $verificationUrl = rtrim($frontendUrl, '/') . '/verify?token=' . $this->token;

            $response = Http::withHeaders([
                'X-Auth-Token' => $authToken,
                'Accept'       => 'application/json',
            ])->post($webhookUrl, [
                'name' => $this->name,
                'email' => $this->email,
                'token' => $this->token,
                'verification_url' => $verificationUrl,
                'event' => 'user_created',
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($response->failed()) {
                Log::error('Failed to send user data to n8n: ' . $response->body());
                throw new \RuntimeException('N8n webhook failed for user verification.');
            }
        } catch (\Exception $e) {
            Log::error('Error sending user data to n8n: ' . $e->getMessage());
            throw $e;
        }
    }
}
