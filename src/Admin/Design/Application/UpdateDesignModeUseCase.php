<?php

namespace Src\Admin\Design\Application;

use Src\Admin\Design\Domain\DesignRepositoryContract;

class UpdateDesignModeUseCase
{
    private DesignRepositoryContract $repository;

    public function __construct(DesignRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $designId, string $displayMode): void
    {
        $this->repository->updateDesignMode($designId, $displayMode);
    }
}
