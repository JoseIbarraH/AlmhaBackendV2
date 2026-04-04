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

    public function execute(string $lang, int $page = 1, int $perPage = 15, ?string $search = null): array
    {
        return $this->repository->getAllByLang($lang, $page, $perPage, $search);
    }
}
