<?php

declare(strict_types=1);

namespace Src\Admin\Trash\Infrastructure\Repositories;

use Src\Admin\Trash\Domain\Contracts\TrashRepositoryContract;
use Src\Admin\Trash\Domain\Entity\TrashItem;
use Src\Admin\Blog\Infrastructure\Models\BlogEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureEloquentModel;
use Src\Admin\Team\Infrastructure\Models\TeamEloquentModel;
use Src\Shared\Infrastructure\Support\MediaUrl;
use App\Models\User as EloquentUserModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use DateTime;

final class EloquentTrashRepository implements TrashRepositoryContract
{
    private array $models = [
        'blog' => BlogEloquentModel::class,
        'procedure' => ProcedureEloquentModel::class,
        'team' => TeamEloquentModel::class,
        'user' => EloquentUserModel::class,
    ];

    public function getAllDeleted(int $page = 1, int $perPage = 15): array
    {
        $trashItems = new Collection();

        foreach ($this->models as $type => $modelClass) {
            // Requerimos que el modelo use SoftDeletes
            if (!in_array(SoftDeletes::class, class_uses_recursive($modelClass))) {
                continue;
            }

            $deletedRecords = $modelClass::onlyTrashed()
                ->with($this->getRelationships($type))
                ->get();

            foreach ($deletedRecords as $record) {
                $trashItems->push(new TrashItem(
                    $record->id,
                    $type,
                    $this->getDisplayName($type, $record),
                    $record->deleted_at ? new DateTime($record->deleted_at->toDateTimeString()) : null
                ));
            }
        }

        $sortedItems = $trashItems->sortByDesc(fn($item) => $item->deletedAt())->values();
        $total = $sortedItems->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $items = $sortedItems->slice(($page - 1) * $perPage, $perPage)->values()->toArray();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );

        return [
            'items' => $items,
            'meta' => collect($paginator->toArray())->except('data')->toArray()
        ];
    }

    public function restore(string $type, string|int $id): void
    {
        $modelClass = $this->models[$type] ?? null;

        if (!$modelClass) {
            throw new \RuntimeException("Invalid trash type: $type");
        }

        $record = $modelClass::onlyTrashed()->find($id);

        if ($record) {
            $record->restore();
        }
    }

    public function permanentlyDelete(string $type, string|int $id): void
    {
        $modelClass = $this->models[$type] ?? null;

        if (!$modelClass) {
            throw new \RuntimeException("Invalid trash type: $type");
        }

        $record = $modelClass::onlyTrashed()
            ->with($this->getMediaRelationships($type))
            ->find($id);

        if ($record) {
            // Collect every S3 file tied to this record before we destroy the DB row.
            $this->deleteMedia($type, $record);
            $record->forceDelete();
        }
    }

    /**
     * Which relationships to eager-load when preparing the S3 cleanup list.
     */
    private function getMediaRelationships(string $type): array
    {
        return match ($type) {
            'procedure' => ['sections', 'gallery'],
            'team'      => ['images'],
            default     => [],
        };
    }

    /**
     * Removes every S3 file owned by the record. Runs before forceDelete() so
     * there are no orphans in the bucket when the DB row goes away.
     */
    private function deleteMedia(string $type, Model $record): void
    {
        $paths = [];

        switch ($type) {
            case 'blog':
                if (!empty($record->image)) {
                    $paths[] = $record->image;
                }
                break;

            case 'procedure':
                if (!empty($record->image)) {
                    $paths[] = $record->image;
                }
                foreach ($record->sections ?? [] as $section) {
                    if (!empty($section->image)) {
                        $paths[] = $section->image;
                    }
                }
                foreach ($record->gallery ?? [] as $gallery) {
                    if (!empty($gallery->path)) {
                        $paths[] = $gallery->path;
                    }
                }
                break;

            case 'team':
                if (!empty($record->image)) {
                    $paths[] = $record->image;
                }
                foreach ($record->images ?? [] as $image) {
                    if (!empty($image->path)) {
                        $paths[] = $image->path;
                    }
                }
                break;

            // 'user' has no media attached in this schema
        }

        if ($paths === []) {
            return;
        }

        $disk = Storage::disk('s3');
        foreach ($paths as $raw) {
            $relative = MediaUrl::toRelativePath($raw);
            if ($relative === '') {
                continue;
            }
            try {
                if ($disk->exists($relative)) {
                    $disk->delete($relative);
                }
            } catch (\Throwable $e) {
                // Never block the force-delete because S3 hiccuped — log and move on.
                Log::warning("Failed to delete S3 object {$relative}: " . $e->getMessage());
            }
        }
    }

    private function getRelationships(string $type): array
    {
        return match ($type) {
            'blog', 'procedure' => ['translations'],
            default => [],
        };
    }

    private function getDisplayName(string $type, Model $record): string
    {
        return match ($type) {
            'blog', 'procedure' => $record->translations->first()?->title ?? "ID: {$record->id}",
            'team', 'user' => $record->name ?? "ID: {$record->id}",
            default => "ID: {$record->id}",
        };
    }
}
