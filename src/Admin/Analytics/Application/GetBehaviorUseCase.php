<?php

declare(strict_types=1);

namespace Src\Admin\Analytics\Application;

use Spatie\Analytics\Period;
use Src\Admin\Analytics\Domain\Contracts\AnalyticsRepositoryContract;

final class GetBehaviorUseCase
{
    private AnalyticsRepositoryContract $repository;

    public function __construct(AnalyticsRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(Period $period): array
    {
        return $this->repository->getBehavior($period);
    }
}
