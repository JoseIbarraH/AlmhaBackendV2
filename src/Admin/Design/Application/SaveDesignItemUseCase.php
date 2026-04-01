<?php

namespace Src\Admin\Design\Application;

use Illuminate\Support\Facades\Storage;
use Src\Admin\Design\Domain\DesignRepositoryContract;

class SaveDesignItemUseCase
{
    private DesignRepositoryContract $repository;

    public function __construct(DesignRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data)
    {
        $path = null;
        if (isset($data['media_file'])) {
            $path = $data['media_file']->store('designs', 'public');
            $data['media_path'] = $path;
            
            // Auto detect media type if not provided explicitly
            if (!isset($data['media_type'])) {
                $mimeType = $data['media_file']->getMimeType();
                $data['media_type'] = str_starts_with($mimeType, 'video/') ? 'video' : 'image';
            }
        }

        $item = $this->repository->saveItem($data);
        return $item->toArray();
    }
}
