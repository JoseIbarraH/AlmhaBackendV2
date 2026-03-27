<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Application;

use Src\Admin\Procedure\Domain\Contracts\ProcedureCategoryRepositoryContract;
use RuntimeException;

final class DeleteProcedureCategoryUseCase
{
    private ProcedureCategoryRepositoryContract $repository;

    public function __construct(ProcedureCategoryRepositoryContract $repository)
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
