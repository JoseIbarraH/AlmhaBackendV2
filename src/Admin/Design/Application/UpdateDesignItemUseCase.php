<?php

namespace Src\Admin\Design\Application;

use Illuminate\Support\Facades\Storage;
use Src\Admin\Design\Domain\DesignRepositoryContract;

class UpdateDesignItemUseCase
{
    private DesignRepositoryContract $repository;

    public function __construct(DesignRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $itemId, array $data)
    {
        $item = $this->repository->findItemById($itemId);
        if (!$item) {
            throw new \Exception("Item not found");
        }

        if (isset($data['media_file'])) {
            // Remove old
            if ($item->mediaPath) {
                Storage::disk('public')->delete($item->mediaPath);
            }

            $path = $data['media_file']->store('designs', 'public');
            $data['media_path'] = $path;
            
            // Auto detect media type if not provided explicitly
            if (!isset($data['media_type'])) {
                $mimeType = $data['media_file']->getMimeType();
                $data['media_type'] = str_starts_with($mimeType, 'video/') ? 'video' : 'image';
            }
        }

        $updatedItem = $this->repository->updateItem($itemId, $data);
        return $updatedItem ? $updatedItem->toArray() : null;
    }
}
