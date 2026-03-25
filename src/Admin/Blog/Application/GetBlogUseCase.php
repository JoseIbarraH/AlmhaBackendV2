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

    public function execute(string $slug, string $lang): Blog
    {
        $blog = $this->repository->findBySlug($slug, $lang);

        if (!$blog) {
            throw new RuntimeException("Blog no encontrado con el slug: $slug para el idioma: $lang");
        }

        return $blog;
    }
}
