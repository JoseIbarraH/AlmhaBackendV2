<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait StoresImages
{
    /**
     * Store an image in S3/MinIO and return its public URL.
     *
     * @param UploadedFile $image  The uploaded image file.
     * @param string       $path   The storage path (e.g. "blogs/1/main_image").
     * @param bool         $deleteExisting Whether to delete existing files in the path first.
     */
    protected function storeImage(UploadedFile $image, string $path, bool $deleteExisting = false): string
    {
        $disk = Storage::disk('s3');

        if ($deleteExisting) {
            $disk->deleteDirectory($path);
        }

        $fullPath = rtrim($path, '/') . '/' . $image->getClientOriginalName();

        $disk->put($fullPath, file_get_contents($image->getRealPath()));

        $baseUrl = rtrim(config('filesystems.disks.s3.url') ?? config('filesystems.disks.s3.endpoint'), '/');
        $bucket = config('filesystems.disks.s3.bucket');

        return "{$baseUrl}/{$fullPath}";
    }
}
