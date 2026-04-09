<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Application;

use Src\Admin\Procedure\Domain\Contracts\ProcedureCategoryRepositoryContract;

final class GetAllProcedureCategoriesUseCase
{
    private ProcedureCategoryRepositoryContract $repository;

    public function __construct(ProcedureCategoryRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $lang = 'es', int $page = 1, int $perPage = 15): array
    {
        return $this->repository->getAllByLang($lang, $page, $perPage);
    }
}
