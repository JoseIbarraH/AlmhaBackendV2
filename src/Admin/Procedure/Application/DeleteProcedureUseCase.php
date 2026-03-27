<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Application;

use Src\Admin\Procedure\Domain\Contracts\ProcedureRepositoryContract;

final class DeleteProcedureUseCase
{
    private ProcedureRepositoryContract $repository;

    public function __construct(ProcedureRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): void
    {
        $this->repository->delete($id);
    }
}
