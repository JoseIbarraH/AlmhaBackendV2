<?php

declare(strict_types=1);

namespace Src\Admin\Analytics\Domain\Contracts;

use Spatie\Analytics\Period;

interface AnalyticsRepositoryContract
{
    /**
     * Get primary Dashboard KPIs:
     * Active Users, Page Views, Engaged Sessions, Engagement Rate, Average Session Duration
     */
    public function getKpis(Period $period): array;

    /**
     * Get the application pulse for charting:
     * activeUsers vs newUsers grouped by date
     */
    public function getAppPulse(Period $period): array;

    /**
     * Get user behavior metrics:
     * Most Visited Pages (pagePath, pageTitle), Traffic Sources, and Source/Medium
     */
    public function getBehavior(Period $period): array;

    /**
     * Get audience profile:
     * Devices (deviceCategory), Geography (country, city), and Browsers (browser)
     */
    public function getUserProfile(Period $period): array;

    /**
     * Get valuable actions:
     * Event counts grouped by eventName
     */
    public function getValuableActions(Period $period): array;
}
