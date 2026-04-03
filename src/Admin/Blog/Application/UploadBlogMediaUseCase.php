<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class UploadBlogMediaUseCase
{
    public function execute(UploadedFile $file): string
    {
        // Store the file in public/blogs/media folder
        $path = $file->store('blogs/media', 'public');
        
        // Return public URL to be accessible from the frontend
        return Storage::disk('public')->url($path);
    }
}
