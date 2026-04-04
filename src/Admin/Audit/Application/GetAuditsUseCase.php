<?php

declare(strict_types=1);

namespace Src\Admin\Audit\Application;

use Src\Admin\Audit\Domain\Contracts\AuditRepositoryContract;

final class GetAuditsUseCase
{
    private AuditRepositoryContract $repository;

    public function __construct(AuditRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $page = 1, int $perPage = 15): array
    {
        return $this->repository->getAll($page, $perPage);
    }
}
