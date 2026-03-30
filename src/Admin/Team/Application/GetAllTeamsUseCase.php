<?php

declare(strict_types=1);

namespace Src\Admin\Team\Application;

use Src\Admin\Team\Domain\Contracts\TeamRepositoryContract;

final class GetAllTeamsUseCase
{
    private TeamRepositoryContract $repository;

    public function __construct(TeamRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): array
    {
        return $this->repository->getAll();
    }
}
