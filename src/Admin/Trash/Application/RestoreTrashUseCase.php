<?php

declare(strict_types=1);

namespace Src\Admin\Trash\Application;

use Src\Admin\Trash\Domain\Contracts\TrashRepositoryContract;

final class RestoreTrashUseCase
{
    private TrashRepositoryContract $repository;

    public function __construct(TrashRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $type, string|int $id): void
    {
        $this->repository->restore($type, $id);
    }
}
