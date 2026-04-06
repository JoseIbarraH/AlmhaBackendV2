<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Illuminate\Support\Facades\Storage;

final class DeleteBlogMediaUseCase
{
    public function execute(string $url): void
    {
        // Extract relative path from S3 url
        $baseUrl = config('filesystems.disks.s3.url');
        
        if (str_starts_with($url, $baseUrl)) {
            $path = substr($url, strlen($baseUrl));
            
            if (Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }
            return;
        }

        // Fallback for local storage if needed
        $parsedUrl = parse_url($url, PHP_URL_PATH);
        $prefix = '/storage/';
        if ($parsedUrl && str_starts_with($parsedUrl, $prefix)) {
            $path = substr($parsedUrl, strlen($prefix));
            
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
