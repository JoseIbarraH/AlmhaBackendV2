<?php

namespace Src\Admin\Design\Application;

use Src\Admin\Design\Domain\DesignRepositoryContract;

class DeleteDesignItemUseCase
{
    private DesignRepositoryContract $repository;

    public function __construct(DesignRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $itemId): void
    {
        // El repositorio ya se encarga de borrar el archivo físico
        $this->repository->deleteItem($itemId);
    }
}
