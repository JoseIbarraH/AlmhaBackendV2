<?php

declare(strict_types=1);

namespace Src\Admin\Analytics\Infrastructure\Repositories;

use Illuminate\Support\Facades\Cache;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Src\Admin\Analytics\Domain\Contracts\AnalyticsRepositoryContract;

final class SpatieAnalyticsRepository implements AnalyticsRepositoryContract
{
    public function getDashboardStats(Period $period): array
    {
        $cacheKey = "analytics.dashboard." . $period->startDate->format('Y-m-d') . "." . $period->endDate->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($period) {
            // 1. Metrics (KPIs)
            $kpiData = Analytics::get(
                $period,
                ['activeUsers', 'sessions', 'bounceRate', 'averageSessionDuration', 'screenPageViews']
            )->first();

            $kpis = $kpiData ?? [
                'activeUsers' => 0,
                'sessions' => 0,
                'bounceRate' => 0,
                'averageSessionDuration' => 0,
                'screenPageViews' => 0,
            ];

            // 2. Weekly Traffic (Daily evolution)
            $weeklyTraffic = Analytics::get(
                $period,
                ['sessions'],
                ['date']
            )->toArray();

            // 3. Device & OS Segmentation
            $devices = Analytics::get(
                $period,
                ['sessions'],
                ['deviceCategory']
            )->toArray();

            $os = Analytics::get(
                $period,
                ['sessions'],
                ['operatingSystem']
            );
            $osComparison = $os->filter(fn($item) => in_array($item['operatingSystem'], ['iOS', 'Android']))->values()->toArray();

            // 4. Ranking Tables (Top 5)
            $browsers = Analytics::get(
                $period,
                ['sessions', 'bounceRate'],
                ['browser'],
                5
            )->toArray();

            $acquisition = Analytics::get(
                $period,
                ['sessions'],
                ['sessionSourceMedium'],
                5
            )->toArray();

            $referrers = Analytics::get(
                $period,
                ['sessions'],
                ['pageReferrer'],
                5
            )->toArray();

            // 5. Geography & Social
            $geography = Analytics::get(
                $period,
                ['sessions'],
                ['country'],
                10
            )->toArray();

            $socialNetworks = [
                'Facebook', 'Instagram', 'YouTube', 'LinkedIn', 'Twitter', 'X', 'TikTok', 
                'WhatsApp', 'Pinterest', 'Reddit', 't.co', 'lnkd.in', 'fb.me'
            ];
            
            $socialSources = Analytics::get(
                $period,
                ['sessions'],
                ['sessionSource']
            );

            $socialShare = $socialSources->filter(function ($item) use ($socialNetworks) {
                foreach ($socialNetworks as $network) {
                    if (stripos($item['sessionSource'], $network) !== false) {
                        return true;
                    }
                }
                return false;
            })->values()->toArray();

            return [
                'kpis' => $kpis,
                'weekly_traffic' => $weeklyTraffic,
                'segmentation' => [
                    'devices' => $devices,
                    'os_comparison' => $osComparison,
                ],
                'rankings' => [
                    'browsers' => $browsers,
                    'acquisition' => $acquisition,
                    'referrers' => $referrers,
                ],
                'geography' => $geography,
                'social_share' => $socialShare,
            ];
        });
    }
}
