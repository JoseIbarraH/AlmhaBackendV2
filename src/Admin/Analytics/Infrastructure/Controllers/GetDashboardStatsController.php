<?php

declare(strict_types=1);

namespace Src\Admin\Analytics\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Spatie\Analytics\Period;
use Src\Admin\Analytics\Application\GetDashboardStatsUseCase;

final class GetDashboardStatsController
{
    private GetDashboardStatsUseCase $useCase;

    public function __construct(GetDashboardStatsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $period = $this->getPeriod($request);

        try {
            return response()->json($this->useCase->execute($period));
        } catch (\Throwable $e) {
            // Logs the exact reason Google Analytics rejected us (missing
            // creds, bad property ID, quota, etc.) so you can fix config
            // instead of guessing why the dashboard is empty.
            Log::error('Analytics dashboard failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'period_start' => $period->startDate->toDateString(),
                'period_end'   => $period->endDate->toDateString(),
            ]);

            return response()->json([
                'error'   => 'analytics_unavailable',
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Analytics data is temporarily unavailable. Check the server logs.',
            ], 503);
        }
    }

    private function getPeriod(Request $request): Period
    {
        if ($request->has('start_date') && $request->has('end_date')) {
            return Period::create(
                Carbon::parse($request->input('start_date')),
                Carbon::parse($request->input('end_date'))
            );
        }

        return Period::days(30); // Default to last 30 days for dashboard to ensure data availability
    }
}
