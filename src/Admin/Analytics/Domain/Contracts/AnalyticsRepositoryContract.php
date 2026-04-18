<?php

declare(strict_types=1);

namespace Src\Admin\Analytics\Domain\Contracts;

use Spatie\Analytics\Period;

interface AnalyticsRepositoryContract
{
    /**
     * Get aggregate Dashboard statistics for a single-endpoint view:
     * KPIs, Weekly Traffic, Device/OS Segmentation, Ranking Tables, Geography, and Social Share.
     */
    public function getDashboardStats(Period $period): array;
}
