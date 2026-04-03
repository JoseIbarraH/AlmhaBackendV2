<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Illuminate\Support\Facades\Storage;

final class DeleteBlogMediaUseCase
{
    public function execute(string $url): void
    {
        // Extract relative path from url
        // e.g. "http://localhost:8000/storage/blogs/media/123.jpg" -> "/storage/blogs/media/123.jpg" or "blogs/media/123.jpg"
        $parsedUrl = parse_url($url, PHP_URL_PATH);
        
        if (!$parsedUrl) {
            return;
        }

        $prefix = '/storage/';
        if (str_starts_with($parsedUrl, $prefix)) {
            $path = substr($parsedUrl, strlen($prefix));
            
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
