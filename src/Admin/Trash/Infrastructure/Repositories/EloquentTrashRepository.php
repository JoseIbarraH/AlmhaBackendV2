<?php

declare(strict_types=1);

namespace Src\Admin\Trash\Infrastructure\Repositories;

use Src\Admin\Trash\Domain\Contracts\TrashRepositoryContract;
use Src\Admin\Trash\Domain\Entity\TrashItem;
use Src\Admin\Blog\Infrastructure\Models\BlogEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureEloquentModel;
use Src\Admin\Team\Infrastructure\Models\TeamEloquentModel;
use App\Models\User as EloquentUserModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use DateTime;

final class EloquentTrashRepository implements TrashRepositoryContract
{
    private array $models = [
        'blog' => BlogEloquentModel::class,
        'procedure' => ProcedureEloquentModel::class,
        'team' => TeamEloquentModel::class,
        'user' => EloquentUserModel::class,
    ];

    public function getAllDeleted(): array
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

        return $trashItems->sortByDesc(fn($item) => $item->deletedAt())->values()->toArray();
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

        $record = $modelClass::onlyTrashed()->find($id);

        if ($record) {
            $record->forceDelete();
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
