<?php

namespace Src\Admin\Design\Application;

use Src\Admin\Design\Domain\DesignRepositoryContract;

class UpdateDesignStatusUseCase
{
    private DesignRepositoryContract $repository;

    public function __construct(DesignRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $designId, string $status): void
    {
        $this->repository->updateDesignStatus($designId, $status);
    }
}
