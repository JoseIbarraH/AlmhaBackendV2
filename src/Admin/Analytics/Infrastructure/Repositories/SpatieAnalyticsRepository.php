<?php

declare(strict_types=1);

namespace Src\Admin\Analytics\Infrastructure\Repositories;

use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Src\Admin\Analytics\Domain\Contracts\AnalyticsRepositoryContract;

final class SpatieAnalyticsRepository implements AnalyticsRepositoryContract
{
    public function getKpis(Period $period): array
    {
        $data = Analytics::get(
            $period,
            ['activeUsers', 'screenPageViews', 'sessions', 'engagementRate', 'averageSessionDuration'],
            []
        );

        return [
            'metrics' => $data->first() ?? []
        ];
    }

    public function getAppPulse(Period $period): array
    {
        $daily = Analytics::get(
            $period,
            ['activeUsers', 'newUsers'],
            ['date']
        );

        $weekly = Analytics::get(
            $period,
            ['activeUsers', 'newUsers'],
            ['yearWeek']
        );

        $monthly = Analytics::get(
            $period,
            ['activeUsers', 'newUsers'],
            ['yearMonth']
        );

        $yearly = Analytics::get(
            $period,
            ['activeUsers', 'newUsers'],
            ['year']
        );

        $dayOfWeek = Analytics::get(
            $period,
            ['activeUsers', 'newUsers'],
            ['dayOfWeek']
        );

        return [
            'pulse' => [
                'daily' => $daily->toArray(),
                'weekly' => $weekly->toArray(),
                'day_of_week' => $dayOfWeek->toArray(),
                'monthly' => $monthly->toArray(),
                'yearly' => $yearly->toArray(),
            ]
        ];
    }

    public function getBehavior(Period $period): array
    {
        $pages = Analytics::get(
            $period,
            ['screenPageViews'],
            ['pagePath', 'pageTitle'],
            10
        );

        $channelGroup = Analytics::get(
            $period,
            ['sessions'],
            ['sessionDefaultChannelGroup']
        );

        $sourceMedium = Analytics::get(
            $period,
            ['sessions'],
            ['sessionSource', 'sessionMedium'],
            15
        );

        return [
            'top_pages' => $pages->toArray(),
            'channel_groups' => $channelGroup->toArray(),
            'source_mediums' => $sourceMedium->toArray()
        ];
    }

    public function getUserProfile(Period $period): array
    {
        $devices = Analytics::get(
            $period,
            ['activeUsers'],
            ['deviceCategory']
        );

        $geography = Analytics::get(
            $period,
            ['activeUsers'],
            ['country', 'city'],
            20
        );

        $browsers = Analytics::get(
            $period,
            ['activeUsers'],
            ['browser']
        );

        return [
            'devices' => $devices->toArray(),
            'geography' => $geography->toArray(),
            'browsers' => $browsers->toArray()
        ];
    }

    public function getValuableActions(Period $period): array
    {
        $events = Analytics::get(
            $period,
            ['eventCount'],
            ['eventName']
        );

        return [
            'events' => $events->toArray()
        ];
    }
}
