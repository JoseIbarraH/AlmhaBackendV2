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

    public function execute(): array
    {
        return $this->repository->getAll();
    }
}
