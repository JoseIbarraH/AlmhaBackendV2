<?php

namespace Src\Admin\Design\Infrastructure\Repositories;

use Illuminate\Support\Facades\Storage;
use Src\Admin\Design\Domain\Design;
use Src\Admin\Design\Domain\DesignItem;
use Src\Admin\Design\Domain\DesignTranslation;
use Src\Admin\Design\Domain\DesignRepositoryContract;
use Src\Admin\Design\Infrastructure\Models\EloquentDesignModel;
use Src\Admin\Design\Infrastructure\Models\EloquentDesignItemModel;

class EloquentDesignRepository implements DesignRepositoryContract
{
    public function findAll(): array
    {
        $eloquentDesigns = EloquentDesignModel::with(['items.translations'])->get();

        $designs = [];
        foreach ($eloquentDesigns as $model) {
            $designs[] = $this->toEntity($model);
        }

        return $designs;
    }

    public function findByKey(string $key): ?Design
    {
        $model = EloquentDesignModel::with(['items.translations'])->where('key', $key)->first();
        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findById(int $id): ?Design
    {
        $model = EloquentDesignModel::with(['items.translations'])->find($id);
        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findItemById(int $itemId): ?DesignItem
    {
        $model = EloquentDesignItemModel::with('translations')->find($itemId);
        if (!$model) {
            return null;
        }

        return $this->toItemEntity($model);
    }

    public function updateDesignMode(int $designId, string $displayMode): void
    {
        EloquentDesignModel::where('id', $designId)->update([
            'display_mode' => $displayMode
        ]);
    }

    public function saveItem(array $data): ?DesignItem
    {
        $itemModel = EloquentDesignItemModel::create([
            'design_id' => $data['design_id'],
            'media_type' => $data['media_type'],
            'media_path' => $data['media_path'] ?? null,
            'order' => $data['order'] ?? 0,
            'status' => $data['status'] ?? 'active',
        ]);

        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $t) {
                $itemModel->translations()->create([
                    'lang' => $t['lang'],
                    'title' => $t['title'] ?? null,
                    'subtitle' => $t['subtitle'] ?? null,
                ]);
            }
        }

        $itemModel->load('translations');
        return $this->toItemEntity($itemModel);
    }

    public function updateItem(int $itemId, array $data): ?DesignItem
    {
        $itemModel = EloquentDesignItemModel::find($itemId);
        if (!$itemModel) {
            return null;
        }

        $updateData = [];
        if (isset($data['media_type'])) $updateData['media_type'] = $data['media_type'];
        if (isset($data['media_path'])) $updateData['media_path'] = $data['media_path'];
        if (isset($data['order'])) $updateData['order'] = $data['order'];
        if (isset($data['status'])) $updateData['status'] = $data['status'];
        
        if (!empty($updateData)) {
            $itemModel->update($updateData);
        }

        if (isset($data['translations']) && is_array($data['translations'])) {
            // Delete old translations to keep it fresh
            $itemModel->translations()->delete();
            foreach ($data['translations'] as $t) {
                $itemModel->translations()->create([
                    'lang' => $t['lang'],
                    'title' => $t['title'] ?? null,
                    'subtitle' => $t['subtitle'] ?? null,
                ]);
            }
        }

        $itemModel->load('translations');
        return $this->toItemEntity($itemModel);
    }

    public function deleteItem(int $itemId): void
    {
        $itemModel = EloquentDesignItemModel::find($itemId);
        if ($itemModel) {
            // Delete media file if exists
            if ($itemModel->media_path) {
                Storage::disk('public')->delete($itemModel->media_path);
            }
            $itemModel->delete(); // Cascades translations
        }
    }

    private function toEntity(EloquentDesignModel $model): Design
    {
        $items = [];
        foreach ($model->items as $itemModel) {
            $items[] = $this->toItemEntity($itemModel);
        }

        return new Design(
            $model->id,
            $model->key,
            $model->display_mode,
            $model->status,
            $items
        );
    }

    private function toItemEntity(EloquentDesignItemModel $itemModel): DesignItem
    {
        $translations = [];
        if ($itemModel->relationLoaded('translations')) {
            foreach ($itemModel->translations as $t) {
                $translations[] = new DesignTranslation(
                    $t->id,
                    $t->lang,
                    $t->title,
                    $t->subtitle
                );
            }
        }

        return new DesignItem(
            $itemModel->id,
            $itemModel->design_id,
            $itemModel->media_type,
            $itemModel->media_path,
            $itemModel->order,
            $itemModel->status,
            $translations
        );
    }
}
