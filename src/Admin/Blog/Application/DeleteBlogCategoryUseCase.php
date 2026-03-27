<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Src\Admin\Blog\Domain\Contracts\BlogCategoryRepositoryContract;
use RuntimeException;

final class DeleteBlogCategoryUseCase
{
    private BlogCategoryRepositoryContract $repository;

    public function __construct(BlogCategoryRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): void
    {
        $category = $this->repository->findById($id);

        if (!$category) {
            throw new RuntimeException("Category not found with ID: $id");
        }

        $this->repository->delete($id);
    }
}
