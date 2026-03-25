<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Src\Admin\Blog\Domain\Contracts\BlogRepositoryContract;
use Src\Admin\Blog\Domain\Entity\Blog;
use RuntimeException;

final class GetBlogUseCase
{
    private BlogRepositoryContract $repository;

    public function __construct(BlogRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, string $lang): Blog
    {
        $blog = $this->repository->findById($id);

        if (!$blog) {
            throw new RuntimeException("Blog no encontrado con el ID: $id");
        }

        $translation = $blog->getTranslation($lang);

        if (!$translation) {
            throw new RuntimeException("Traducción no encontrada para el idioma: $lang en el blog ID: $id");
        }

        // Devolvemos el blog pero con una sola traducción (la solicitada)
        // Esto depende de si el frontend espera un array o un objeto único. 
        // Basándonos en la entidad, es un array.
        $blogWithSingleTranslation = new Blog(
            $blog->categoryCode(),
            $blog->status(),
            $blog->userId(),
            $blog->image(),
            $blog->writer(),
            $blog->views(),
            $blog->publishedAt(),
            $blog->notificationSentAt(),
            [$translation],
            $blog->id()
        );

        return $blogWithSingleTranslation;
    }
}
