<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Illuminate\Support\Facades\Storage;
use Src\Shared\Infrastructure\Support\MediaUrl;

final class DeleteBlogMediaUseCase
{
    /**
     * Deletes an image from S3 given either:
     *   - a full URL ("https://media.almha.../blog/1/img.jpg")
     *   - a relative path ("blog/1/img.jpg")
     *
     * Both formats coexist because older data stored URLs while newer uploads
     * store paths. MediaUrl::toRelativePath() normalizes them for the disk.
     */
    public function execute(string $urlOrPath): void
    {
        $path = MediaUrl::toRelativePath($urlOrPath);
        if ($path === '') {
            return;
        }

        $disk = Storage::disk('s3');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
