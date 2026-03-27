<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Src\Admin\Blog\Domain\Contracts\BlogCategoryRepositoryContract;
use Src\Admin\Blog\Domain\Entity\BlogCategory;
use Src\Admin\Blog\Domain\Entity\BlogCategoryTranslation;
use Src\Admin\Blog\Infrastructure\Models\BlogCategoryEloquentModel;

final class EloquentBlogCategoryRepository implements BlogCategoryRepositoryContract
{
    private BlogCategoryEloquentModel $model;

    public function __construct(BlogCategoryEloquentModel $model)
    {
        $this->model = $model;
    }

    public function save(BlogCategory $category): void
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

    public function findById(int $id): ?BlogCategory
    {
        $eloquentCat = $this->model->with('translations')->find($id);

        if (!$eloquentCat) {
            return null;
        }

        return $this->mapToDomain($eloquentCat);
    }

    public function findByCode(string $code): ?BlogCategory
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

    public function update(BlogCategory $category): void
    {
        DB::transaction(function () use ($category) {
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
            $eloquentCategory = $this->model->find($id);
            if ($eloquentCategory) {
                $eloquentCategory->translations()->delete();
                $eloquentCategory->delete();
            }
        });
    }

    private function mapToDomain($eloquentCat): BlogCategory
    {
        $translations = $eloquentCat->translations->map(function ($t) {
            return new BlogCategoryTranslation(
                $t->lang,
                $t->title,
                $t->id
            );
        })->toArray();

        return new BlogCategory(
            $eloquentCat->code,
            $translations,
            $eloquentCat->id
        );
    }
}
