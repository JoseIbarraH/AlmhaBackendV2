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

    public function getAll(): array
    {
        $categories = $this->model->with('translations')->get();

        return $categories->map(function ($eloquentCat) {
            return $this->mapToDomain($eloquentCat);
        })->toArray();
    }

    private function mapToDomain($eloquentCat): ProcedureCategory
    {
        $translations = $eloquentCat->translations->map(function ($t) {
            return new ProcedureCategoryTranslation(
                $t->lang,
                $t->title,
                $t->id
            );
        })->toArray();

        return new ProcedureCategory(
            $eloquentCat->code,
            $translations,
            $eloquentCat->id
        );
    }

}
