<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Src\Admin\Blog\Domain\Entity\BlogCategory;
use Src\Admin\Procedure\Domain\Contracts\ProcedureCategoryRepositoryContract;
use Src\Admin\Procedure\Domain\Entity\ProcedureCategory;
use Src\Admin\Procedure\Domain\Entity\ProcedureCategoryTranslation;
use Src\Admin\Procedure\Infrastructure\Models\ProcedureCategoryEloquentModel;



final class EloquentProcedureCategoryRepository implements ProcedureCategoryRepositoryContract
{
    private ProcedureCategoryEloquentModel $model;

    public function __construct(ProcedureCategoryEloquentModel $model)
    {
        $this->model = $model;
    }

    public function save(ProcedureCategory $category): void
    {
        DB::transaction(function () use ($category) {
            $eloquentCategory = $this->model->create([
                'code' => $category->code()
            ]);

            foreach ($category->translations() as $translation) {
                $eloquentCategory->translations()->create([
                    'lang' => $translation->lang(),
                    'title' => $translation->title()
                ]);
            }
        });
    }

    public function findById(int $id): ?ProcedureCategory
    {
        $eloquentCat = $this->model->with('translations')->find($id);

        if (!$eloquentCat) {
            return null;
        }

        return $this->mapToDomain($eloquentCat);
    }

    public function findByCode(string $code): ?ProcedureCategory
    {
        $eloquentCat = $this->model->with('translations')->where('code', $code)->first();

        if (!$eloquentCat) {
            return null;
        }

        return $this->mapToDomain($eloquentCat);
    }

    public function getAll(int $page = 1, int $perPage = 15): array
    {
        $paginator = $this->model->with('translations')->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($eloquentCat) {
            return $this->mapToDomain($eloquentCat);
        })->toArray();

        return [
            'items' => $items,
            'meta' => collect($paginator->toArray())->except('data')->toArray()
        ];
    }

    public function getAllByLang(string $lang, int $page = 1, int $perPage = 15): array
    {
        // Traemos todas las traducciones para que mapToDomain pueda hacer el fallback si no existe
        // la traducción en el $lang específico.
        $paginator = $this->model->with('translations')->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($eloquentCat) use ($lang) {
            return $this->mapToDomain($eloquentCat, $lang);
        })->toArray();

        return [
            'items' => $items,
            'meta' => collect($paginator->toArray())->except('data')->toArray()
        ];
    }

    public function update(ProcedureCategory $category): void
    {
        DB::transaction(function () use ($category) {
            /** @var ProcedureCategoryEloquentModel|null $eloquentCategory */
            $eloquentCategory = $this->model->find($category->id());
            if ($eloquentCategory) {
                $eloquentCategory->update([
                    'code' => $category->code()
                ]);

                // Sync translations
                $eloquentCategory->translations()->delete();
                foreach ($category->translations() as $translation) {
                    $eloquentCategory->translations()->create([
                        'lang' => $translation->lang(),
                        'title' => $translation->title()
                    ]);
                }
            }
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            /** @var ProcedureCategoryEloquentModel|null $eloquentCategory */
            $eloquentCategory = $this->model->find($id);
            if ($eloquentCategory) {
                $eloquentCategory->translations()->delete();
                $eloquentCategory->delete();
            }
        });
    }

    private function mapToDomain($eloquentCat, ?string $lang = null): ProcedureCategory
    {
        $translations = $eloquentCat->translations->map(function ($t) {
            return new ProcedureCategoryTranslation(
                $t->lang,
                $t->title,
                $t->id
            );
        })->toArray();

        $localizedName = null;
        if ($lang) {
            $translationModel = $eloquentCat->translations->firstWhere('lang', $lang) 
                                ?? $eloquentCat->translations->first();
            $localizedName = $translationModel?->title;
        }

        return new ProcedureCategory(
            $eloquentCat->code,
            $translations,
            $eloquentCat->id,
            $localizedName
        );
    }

}
