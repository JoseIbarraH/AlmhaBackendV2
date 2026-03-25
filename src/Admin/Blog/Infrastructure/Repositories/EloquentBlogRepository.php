<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Src\Admin\Blog\Domain\Contracts\BlogRepositoryContract;
use Src\Admin\Blog\Domain\Entity\Blog;
use Src\Admin\Blog\Domain\Entity\BlogTranslation;
use Src\Admin\Blog\Infrastructure\Models\BlogEloquentModel;

final class EloquentBlogRepository implements BlogRepositoryContract
{
    private BlogEloquentModel $model;

    public function __construct(BlogEloquentModel $model)
    {
        $this->model = $model;
    }

    public function save(Blog $blog): void
    {
        DB::transaction(function () use ($blog) {
            $eloquentBlog = $this->model->create([
                'user_id' => $blog->userId(),
                'slug' => $blog->slug(),
                'image' => $blog->image(),
                'category_code' => $blog->categoryCode(),
                'writer' => $blog->writer(),
                'views' => $blog->views(),
                'status' => $blog->status(),
                'published_at' => $blog->publishedAt(),
                'notification_sent_at' => $blog->notificationSentAt(),
            ]);

            foreach ($blog->translations() as $translation) {
                $eloquentBlog->translations()->create([
                    'lang' => $translation->lang(),
                    'title' => $translation->title(),
                    'content' => $translation->content()
                ]);
            }
        });
    }

    public function findById(int $id): ?Blog
    {
        $eloquentBlog = $this->model->with('translations')->find($id);

        if (!$eloquentBlog) {
            return null;
        }

        $translations = $eloquentBlog->translations->map(function ($t) {
            return new BlogTranslation(
                $t->lang,
                $t->title,
                $t->content,
                $t->id
            );
        })->toArray();

        return new Blog(
            $eloquentBlog->slug,
            $eloquentBlog->category_code,
            $eloquentBlog->status,
            $eloquentBlog->user_id,
            $eloquentBlog->image,
            $eloquentBlog->writer,
            $eloquentBlog->views,
            $eloquentBlog->published_at,
            $eloquentBlog->notification_sent_at,
            $translations,
            $eloquentBlog->id
        );
    }

    public function update(Blog $blog): void
    {
        if ($blog->id() === null) {
            return;
        }

        DB::transaction(function () use ($blog) {
            $eloquentBlog = $this->model->find($blog->id());
            
            if ($eloquentBlog) {
                $eloquentBlog->update([
                    'user_id' => $blog->userId(),
                    'slug' => $blog->slug(),
                    'image' => $blog->image(),
                    'category_code' => $blog->categoryCode(),
                    'writer' => $blog->writer(),
                    'views' => $blog->views(),
                    'status' => $blog->status(),
                    'published_at' => $blog->publishedAt(),
                    'notification_sent_at' => $blog->notificationSentAt(),
                ]);

                // Sync Translations: simple approach is to delete and recreate or update existing.
                // For a robust system, we recreate them all (if domain logic provides them all) or just update them individually.
                $eloquentBlog->translations()->delete();
                foreach ($blog->translations() as $translation) {
                    $eloquentBlog->translations()->create([
                        'lang' => $translation->lang(),
                        'title' => $translation->title(),
                        'content' => $translation->content()
                    ]);
                }
            }
        });
    }

    public function delete(int $id): void
    {
        $eloquentBlog = $this->model->find($id);
        if ($eloquentBlog) {
            $eloquentBlog->delete();
        }
    }

    public function getAll(): array
    {
        $blogs = $this->model->with('translations')->get();

        return $blogs->map(function ($eloquentBlog) {
            $translations = $eloquentBlog->translations->map(function ($t) {
                return new BlogTranslation(
                    $t->lang,
                    $t->title,
                    $t->content,
                    $t->id
                );
            })->toArray();

            return new Blog(
                $eloquentBlog->slug,
                $eloquentBlog->category_code,
                $eloquentBlog->status,
                $eloquentBlog->user_id,
                $eloquentBlog->image,
                $eloquentBlog->writer,
                $eloquentBlog->views,
                $eloquentBlog->published_at,
                $eloquentBlog->notification_sent_at,
                $translations,
                $eloquentBlog->id
            );
        })->toArray();
    }
}
