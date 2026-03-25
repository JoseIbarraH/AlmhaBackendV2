<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Src\Admin\Blog\Domain\Contracts\BlogRepositoryContract;
use RuntimeException;

final class ChangeBlogStatusUseCase
{
    private BlogRepositoryContract $repository;

    public function __construct(BlogRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $blogId, string $newStatus): void
    {
        $blog = $this->repository->findById($blogId);

        if (!$blog) {
            throw new RuntimeException("Blog no encontrado con el ID: $blogId");
        }

        $blog->changeStatus($newStatus);

        $this->repository->update($blog);
    }
}
