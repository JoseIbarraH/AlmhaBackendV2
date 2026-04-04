<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Src\Admin\Blog\Domain\Contracts\BlogCategoryRepositoryContract;

final class GetAllBlogCategoriesUseCase
{
    private BlogCategoryRepositoryContract $repository;

    public function __construct(BlogCategoryRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $page = 1, int $perPage = 15): array
    {
        return $this->repository->getAll($page, $perPage);
    }
}
