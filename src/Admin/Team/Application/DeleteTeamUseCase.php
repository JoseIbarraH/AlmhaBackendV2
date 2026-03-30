<?php

declare(strict_types=1);

namespace Src\Admin\Team\Application;

use Src\Admin\Team\Domain\Contracts\TeamRepositoryContract;

final class DeleteTeamUseCase
{
    private TeamRepositoryContract $repository;

    public function __construct(TeamRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): void
    {
        $this->repository->delete($id);
    }
}
