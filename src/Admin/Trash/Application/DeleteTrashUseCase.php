<?php

declare(strict_types=1);

namespace Src\Admin\Trash\Application;

use Src\Admin\Trash\Domain\Contracts\TrashRepositoryContract;

final class DeleteTrashUseCase
{
    private TrashRepositoryContract $repository;

    public function __construct(TrashRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $type, string|int $id): void
    {
        $this->repository->permanentlyDelete($type, $id);
    }
}
