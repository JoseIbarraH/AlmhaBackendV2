<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Application;

use Src\Admin\Procedure\Domain\Contracts\ProcedureRepositoryContract;
use Src\Admin\Procedure\Domain\Entity\Procedure;

final class GetProcedureUseCase
{
    private ProcedureRepositoryContract $repository;

    public function __construct(ProcedureRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, ?string $lang = null): ?Procedure
    {
        return $this->repository->findById($id, $lang);
    }
}
