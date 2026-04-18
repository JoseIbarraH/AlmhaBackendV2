<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Illuminate\Support\Facades\Log;

class TestAnalyticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:test {days=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Google Analytics 4 connectivity and raw data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->argument('days');
        $this->info("Testing GA4 for the last {$days} days...");

        $period = Period::days($days);

        try {
            $this->info("--- Property ID: " . config('analytics.property_id') . " ---");

            // Test 1: Simple Metrics
            $this->comment("1. Testing simple metrics (activeUsers, sessions)...");
            $metrics = ['activeUsers', 'sessions', 'screenPageViews'];
            $data = Analytics::get($period, $metrics);
            $this->logAndShow($data);

            // Test 2: Dimensions
            $this->comment("2. Testing dimensions (date, deviceCategory)...");
            $data = Analytics::get($period, ['sessions'], ['date', 'deviceCategory'], 5);
            $this->logAndShow($data);

            // Test 3: The Dashboard Metric List
            $this->comment("3. Testing Dashboard full metric list...");
            $dashboardMetrics = ['activeUsers', 'sessions', 'bounceRate', 'averageSessionDuration', 'screenPageViews'];
            try {
                $data = Analytics::get($period, $dashboardMetrics);
                $this->logAndShow($data);
            } catch (\Exception $e) {
                $this->error("Dashboard metrics failed: " . $e->getMessage());
            }

            $this->info("Test completed.");

        } catch (\Exception $e) {
            $this->error("Critical Error: " . $e->getMessage());
            Log::error("GA4 Test Error: " . $e->getMessage());
        }
    }

    private function logAndShow($data)
    {
        if ($data->isEmpty()) {
            $this->warn("Result: EMPTY (0 rows)");
        } else {
            $this->info("Result: " . count($data) . " rows found.");
            $this->line(json_encode($data->toArray(), JSON_PRETTY_PRINT));
        }
        $this->line("");
    }
}
