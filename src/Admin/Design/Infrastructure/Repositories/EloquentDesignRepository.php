<?php

namespace Src\Admin\Design\Infrastructure\Repositories;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Src\Admin\Design\Domain\Design;
use Src\Admin\Design\Domain\DesignItem;
use Src\Admin\Design\Domain\DesignTranslation;
use Src\Admin\Design\Domain\DesignRepositoryContract;
use Src\Admin\Design\Infrastructure\Models\EloquentDesignModel;
use Src\Admin\Design\Infrastructure\Models\EloquentDesignItemModel;

class EloquentDesignRepository implements DesignRepositoryContract
{
    public function findAll(?string $lang = null): array
    {
        $eloquentDesigns = EloquentDesignModel::with(['items.translations'])->get();

        $designs = [];
        /** @var EloquentDesignModel $model */
        foreach ($eloquentDesigns as $model) {
            $designs[] = $this->toEntity($model, $lang);
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

    public function findItemById(int $itemId, ?string $lang = null): ?DesignItem
    {
        $model = EloquentDesignItemModel::with('translations')->find($itemId);
        if (!$model) {
            return null;
        }

        return $this->toItemEntity($model, $lang);
    }

    public function updateDesignMode(int $designId, string $displayMode): void
    {
        EloquentDesignModel::where('id', $designId)->update([
            'display_mode' => $displayMode
        ]);
    }

    public function updateDesignStatus(int $designId, string $status): void
    {
        EloquentDesignModel::where('id', $designId)->update([
            'status' => $status
        ]);
    }

    public function saveItem(array $data, ?string $lang = null): ?DesignItem
    {
        return DB::transaction(function () use ($data, $lang) {
            $lang = $lang ? substr($lang, 0, 2) : null;
            $itemModel = EloquentDesignItemModel::create([
                'design_id' => $data['design_id'],
                'media_type' => $data['media_type'],
                'media_path' => $data['media_path'] ?? null,
                'order' => $data['order'] ?? 0,
                'status' => $data['status'] ?? 'active',
            ]);

            // Ensure we have at least default translations if none provided
            $translations = $data['translations'] ?? [
                ['lang' => 'es', 'title' => '', 'subtitle' => ''],
                ['lang' => 'en', 'title' => '', 'subtitle' => '']
            ];

            if (is_array($translations)) {
                foreach ($translations as $t) {
                    $itemModel->translations()->create([
                        'lang' => $t['lang'],
                        'title' => $t['title'] ?? null,
                        'subtitle' => $t['subtitle'] ?? null,
                    ]);
                }
            }

            $itemModel->load('translations');
            return $this->toItemEntity($itemModel, $lang);
        });
    }

    public function updateItem(int $itemId, array $data, ?string $lang = null): ?DesignItem
    {
        return DB::transaction(function () use ($itemId, $data, $lang) {
            $lang = $lang ? substr($lang, 0, 2) : null;
            /** @var EloquentDesignItemModel|null $itemModel */
            $itemModel = EloquentDesignItemModel::find($itemId);
            if (!$itemModel) {
                return null;
            }

            $itemModel->update(array_filter([
                'media_type' => $data['media_type'] ?? null,
                'media_path' => $data['media_path'] ?? null,
                'order' => $data['order'] ?? null,
                'status' => $data['status'] ?? null,
            ]));

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
            return $this->toItemEntity($itemModel, $lang);
        });
    }

    public function deleteItem(int $itemId): void
    {
        /** @var EloquentDesignItemModel|null $itemModel */
        $itemModel = EloquentDesignItemModel::find($itemId);
        if ($itemModel) {
            // Delete media file if exists (handle both full URLs and relative paths)
            if ($itemModel->media_path) {
                $path = \Src\Shared\Infrastructure\Support\MediaUrl::toRelativePath($itemModel->media_path);
                if ($path !== '') {
                    Storage::disk('s3')->delete($path);
                }
            }
            $itemModel->delete(); // Cascades translations
        }
    }

    private function toEntity(EloquentDesignModel $model, ?string $lang = null): Design
    {
        $items = [];
        foreach ($model->items as $itemModel) {
            $items[] = $this->toItemEntity($itemModel, $lang);
        }

        return new Design(
            $model->id,
            $model->key,
            $model->display_mode,
            $model->status,
            $items
        );
    }

    private function toItemEntity(EloquentDesignItemModel $itemModel, ?string $lang = null): DesignItem
    {
        $translations = [];
        $localizedTitle = null;
        $localizedSubtitle = null;

        if ($itemModel->relationLoaded('translations')) {
            foreach ($itemModel->translations as $t) {
                $translations[] = new DesignTranslation(
                    $t->id,
                    $t->lang,
                    $t->title,
                    $t->subtitle
                );

                if ($lang && $t->lang === substr($lang, 0, 2)) {
                    $localizedTitle = $t->title;
                    $localizedSubtitle = $t->subtitle;
                }
            }
        }

        return new DesignItem(
            $itemModel->id,
            $itemModel->design_id,
            $itemModel->media_type,
            $itemModel->media_path,
            $itemModel->order,
            $itemModel->status,
            $translations,
            $localizedTitle,
            $localizedSubtitle
        );
    }
}
