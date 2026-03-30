<?php

declare(strict_types=1);

namespace Src\Admin\Team\Application;

use Src\Admin\Team\Domain\Contracts\TeamRepositoryContract;
use Src\Admin\Team\Domain\Entity\Team;

final class GetTeamUseCase
{
    private TeamRepositoryContract $repository;

    public function __construct(TeamRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): ?Team
    {
        return $this->repository->findById($id);
    }
}
