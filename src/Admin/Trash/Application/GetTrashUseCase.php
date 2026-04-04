<?php

declare(strict_types=1);

namespace Src\Admin\Trash\Application;

use Src\Admin\Trash\Domain\Contracts\TrashRepositoryContract;

final class GetTrashUseCase
{
    private TrashRepositoryContract $repository;

    public function __construct(TrashRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $page = 1, int $perPage = 15): array
    {
        return $this->repository->getAllDeleted($page, $perPage);
    }
}
