<?php

namespace Src\Admin\Design\Application;

use Src\Admin\Design\Domain\DesignRepositoryContract;

class GetDesignsUseCase
{
    private DesignRepositoryContract $repository;

    public function __construct(DesignRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): array
    {
        $designs = $this->repository->findAll();
        
        return array_map(fn($design) => $design->toArray(), $designs);
    }
}
