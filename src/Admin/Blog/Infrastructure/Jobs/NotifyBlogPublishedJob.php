<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Jobs;

use App\Mail\BlogPublishedNotificationEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Src\Admin\Blog\Infrastructure\Models\BlogEloquentModel;
use Src\Landing\Subscription\Infrastructure\Models\SubscriberEloquentModel;

final class NotifyBlogPublishedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct(
        private int $blogId,
    ) {}

    public function handle(): void
    {
        /** @var BlogEloquentModel|null $blog */
        $blog = BlogEloquentModel::with('translations')->find($this->blogId);
        if (!$blog || $blog->status !== 'published') {
            return;
        }

        $clientUrl = rtrim((string) config('app.client_url'), '/');
        $apiUrl = rtrim((string) config('app.url'), '/');
        $defaultLang = (string) config('app.locale', 'es');
        $supported = (array) config('app.supported_locales', ['es', 'en', 'fr']);
        if (!in_array($defaultLang, $supported, true)) {
            $defaultLang = $supported[0] ?? 'es';
        }

        $translationsByLang = $blog->translations->keyBy('lang');
        $blogImage = $this->resolveImage((string) $blog->image);

        SubscriberEloquentModel::query()
            ->whereNotNull('verified_at')
            ->whereNull('unsubscribed_at')
            ->select(['id', 'email', 'token'])
            ->chunkById(200, function ($chunk) use ($translationsByLang, $defaultLang, $clientUrl, $apiUrl, $blogImage) {
                foreach ($chunk as $subscriber) {
                    $translation = $translationsByLang[$defaultLang]
                        ?? $translationsByLang->first();

                    if (!$translation) {
                        continue;
                    }

                    $excerpt = Str::limit(trim(strip_tags((string) $translation->content)), 220);
                    $blogUrl = "{$clientUrl}/{$defaultLang}/blog/" . urlencode((string) $translation->slug);
                    $unsubscribeUrl = $apiUrl . '/api/client/subscribe/unsubscribe?token=' . urlencode((string) $subscriber->token) . '&lang=' . $defaultLang;

                    Mail::to($subscriber->email)->queue(new BlogPublishedNotificationEmail(
                        blogTitle: (string) $translation->title,
                        blogExcerpt: $excerpt,
                        blogImage: $blogImage,
                        blogUrl: $blogUrl,
                        unsubscribeUrl: $unsubscribeUrl,
                    ));
                }
            });
    }

    private function resolveImage(string $path): ?string
    {
        if ($path === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        $base = rtrim((string) config('app.url'), '/');
        return $base . '/' . ltrim($path, '/');
    }
}
