<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait StoresImages
{
    /**
     * Store an image on the configured disk and return its RELATIVE path.
     *
     * The relative path is what should be persisted in the DB — presenters
     * (via Src\Shared\Infrastructure\Support\MediaUrl) resolve it to a full
     * URL on read. This keeps DB data portable across environments/buckets.
     *
     * @param UploadedFile $image          The uploaded image file.
     * @param string       $path           Storage directory (e.g. "blogs/1/main_image").
     * @param bool         $deleteExisting Wipe the directory before uploading.
     * @return string Relative path of the stored file (e.g. "blogs/1/main_image/file.jpg").
     */
    protected function storeImage(UploadedFile $image, string $path, bool $deleteExisting = false): string
    {
        $disk = Storage::disk('s3');

        if ($deleteExisting) {
            $disk->deleteDirectory($path);
        }

        $fullPath = rtrim($path, '/') . '/' . $image->getClientOriginalName();

        $disk->put($fullPath, file_get_contents($image->getRealPath()));

        return $fullPath;
    }
}
