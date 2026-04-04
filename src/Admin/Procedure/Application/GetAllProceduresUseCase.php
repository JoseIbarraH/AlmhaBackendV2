<?php

declare(strict_types=1);

namespace Src\Admin\Procedure\Application;

use Src\Admin\Procedure\Domain\Contracts\ProcedureRepositoryContract;

final class GetAllProceduresUseCase
{
    private ProcedureRepositoryContract $repository;

    public function __construct(ProcedureRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $lang, int $page = 1, int $perPage = 15): array
    {
        return $this->repository->getAllByLang($lang, $page, $perPage);
    }
}
