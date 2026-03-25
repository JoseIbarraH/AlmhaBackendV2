<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Src\Admin\Blog\Domain\Contracts\BlogRepositoryContract;

final class GetAllBlogsUseCase
{
    private BlogRepositoryContract $repository;

    public function __construct(BlogRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): array
    {
        return $this->repository->getAll();
    }
}
