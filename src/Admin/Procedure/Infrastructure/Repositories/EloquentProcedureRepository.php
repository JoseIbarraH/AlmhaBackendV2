<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Src\Admin\Procedure\Domain\Contracts\ProcedureRepositoryContract;
use Src\Admin\Procedure\Domain\Entity\Procedure;
use Src\Admin\Procedure\Domain\Entity\ProcedureTranslation;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureEloquentModel;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureTranslationEloquentModel;

final class EloquentProcedureRepository implements ProcedureRepositoryContract
{
    private ProcedureEloquentModel $model;

    public function __construct(ProcedureEloquentModel $model)
    {
        $this->model = $model;
    }

    public function save(Procedure $procedure): int
    {
        $procedureId = 0;

        DB::transaction(function () use ($procedure, &$procedureId) {
            $eloquentProcedure = $this->model->create([
                'user_id' => $procedure->userId(),
                'image' => $procedure->image(),
                'category_code' => $procedure->categoryCode(),
                'status' => $procedure->status(),
                'views' => $procedure->views(),
            ]);

            $procedureId = $eloquentProcedure->id;

            foreach ($procedure->translations() as $translation) {
                $eloquentProcedure->translations()->create([
                    'lang' => $translation->lang(),
                    'title' => $translation->title(),
                    'subtitle' => $translation->subtitle(),
                ]);
            }
        });

        return $procedureId;
    }

    public function update(Procedure $procedure): void
    {
        if ($procedure->id() === null) {
            return;
        }

        DB::transaction(function () use ($procedure) {
            $eloquentProcedure = $this->model->find($procedure->id());

            if ($eloquentProcedure) {
                $eloquentProcedure->update([
                    'user_id' => $procedure->userId(),
                    'image' => $procedure->image(),
                    'category_code' => $procedure->categoryCode(),
                    'status' => $procedure->status(),
                    'views' => $procedure->views(),
                ]);

                // Sync Translations: delete and recreate
                $eloquentProcedure->translations()->delete();
                foreach ($procedure->translations() as $translation) {
                    $eloquentProcedure->translations()->create([
                        'lang' => $translation->lang(),
                        'title' => $translation->title(),
                        'subtitle' => $translation->subtitle(),
                    ]);
                }
            }
        });
    }

    public function findById(int $id): ?Procedure
    {
        $eloquentProcedure = $this->model->with('translations')->find($id);

        if (!$eloquentProcedure) {
            return null;
        }

        return $this->toDomainEntity($eloquentProcedure);
    }

    public function findBySlug(string $slug, string $lang): ?Procedure
    {
        $translationModel = ProcedureTranslationEloquentModel::where('slug', $slug)
            ->where('lang', $lang)
            ->first();

        if (!$translationModel) {
            return null;
        }

        $eloquentProcedure = $this->model->with(['translations' => function ($query) use ($lang) {
            $query->where('lang', $lang);
        }])->find($translationModel->procedure_id);

        if (!$eloquentProcedure) {
            return null;
        }

        return $this->toDomainEntity($eloquentProcedure);
    }

    public function updateImage(int $id, string $imagePath): void
    {
        $eloquentProcedure = $this->model->find($id);
        if ($eloquentProcedure) {
            $eloquentProcedure->update(['image' => $imagePath]);
        }
    }

    public function delete(int $id): void
    {
        $eloquentProcedure = $this->model->find($id);
        if ($eloquentProcedure) {
            $eloquentProcedure->delete();
        }
    }

    public function getAll(): array
    {
        $procedures = $this->model->with('translations')->get();

        return $procedures->map(function ($eloquentProcedure) {
            return $this->toDomainEntity($eloquentProcedure);
        })->toArray();
    }

    public function getAllByLang(string $lang): array
    {
        $procedures = $this->model->with(['translations' => function ($query) use ($lang) {
            $query->where('lang', $lang);
        }])->get();

        return $procedures->map(function ($eloquentProcedure) {
            return $this->toDomainEntity($eloquentProcedure);
        })->toArray();
    }

    /**
     * Convierte un modelo Eloquent a entidad de dominio
     */
    private function toDomainEntity($eloquentProcedure): Procedure
    {
        $translations = $eloquentProcedure->translations->map(function ($t) {
            return new ProcedureTranslation(
                $t->id,
                $t->lang,
                $t->slug,
                $t->title,
                $t->subtitle
            );
        })->toArray();

        return new Procedure(
            $eloquentProcedure->id,
            $eloquentProcedure->user_id,
            $eloquentProcedure->image,
            $eloquentProcedure->category_code,
            $eloquentProcedure->status,
            $eloquentProcedure->views,
            $translations
        );
    }
}
