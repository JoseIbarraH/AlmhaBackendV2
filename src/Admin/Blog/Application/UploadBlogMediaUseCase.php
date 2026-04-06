<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class UploadBlogMediaUseCase
{
    public function execute(int $blogId, UploadedFile $file): string
    {
        // Store the file in s3 bucket under blogs/{id}/media folder
        $path = $file->store("blogs/{$blogId}/media", 's3');
        
        // Return public URL to be accessible from the frontend
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('s3');
        
        return $disk->url($path);
    }
}
