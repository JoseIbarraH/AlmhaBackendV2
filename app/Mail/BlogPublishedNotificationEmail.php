<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;

final class BlogPublishedNotificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public string $blogTitle,
        public string $blogExcerpt,
        public ?string $blogImage,
        public string $blogUrl,
        public string $unsubscribeUrl,
    ) {
        $this->onQueue('mass-mail');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo artículo en Almha — ' . $this->blogTitle,
        );
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => "<{$this->unsubscribeUrl}>",
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client.blog-published',
            with: [
                'blogTitle' => $this->blogTitle,
                'blogExcerpt' => $this->blogExcerpt,
                'blogImage' => $this->blogImage,
                'blogUrl' => $this->blogUrl,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ],
        );
    }

    public function middleware(): array
    {
        return [new RateLimited('mass-mail')];
    }
}
