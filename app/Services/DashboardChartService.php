<?php

namespace App\Services;

use App\Models\AssetCategory;
use App\Models\Deployment;
use Carbon\CarbonImmutable;

/**
 * Aggregates dashboard chart data.
 *
 * Each method returns a plain array shaped as { labels: string[], data: int[] }
 * so the view layer stays free of query logic and the frontend stays free of
 * charting configuration built in PHP.
 */
class DashboardChartService
{
    /**
     * Maximum number of slices the categorical palette supports. Categories
     * beyond this are folded into a single "Other" bucket rather than cycling
     * colors, which would make two different categories share a hue.
     */
    private const MAX_CATEGORY_SLICES = 8;

    /**
     * Asset counts per active category, largest first.
     *
     * Categories with no assets are omitted, and anything past the palette
     * limit is summed into "Other".
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function assetsByCategory(): array
    {
        $categories = AssetCategory::query()
            ->withCount('assets')
            ->where('status', 'active')
            ->whereHas('assets')
            ->orderByDesc('assets_count')
            ->get(['id', 'name']);

        if ($categories->isEmpty()) {
            return ['labels' => [], 'data' => []];
        }

        if ($categories->count() <= self::MAX_CATEGORY_SLICES) {
            return [
                'labels' => $categories->pluck('name')->all(),
                'data' => $categories->pluck('assets_count')->map(fn ($count) => (int) $count)->all(),
            ];
        }

        $visible = $categories->take(self::MAX_CATEGORY_SLICES - 1);
        $otherTotal = (int) $categories->slice(self::MAX_CATEGORY_SLICES - 1)->sum('assets_count');

        return [
            'labels' => [...$visible->pluck('name')->all(), 'Other'],
            'data' => [
                ...$visible->pluck('assets_count')->map(fn ($count) => (int) $count)->all(),
                $otherTotal,
            ],
        ];
    }

    /**
     * Deployment counts per month over the trailing window, zero-filled so
     * months without deployments render as 0 instead of collapsing the axis.
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function monthlyDeploymentTrend(int $months = 12): array
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonths($months - 1);

        // Only the date column is loaded, then bucketed in PHP — this keeps the
        // query driver-agnostic (no DATE_FORMAT/strftime branch) at the cost of
        // one narrow column over a bounded, indexed window.
        $counts = Deployment::query()
            ->where('deployment_date', '>=', $start->toDateString())
            ->pluck('deployment_date')
            ->countBy(fn ($date) => $date->format('Y-m'));

        $labels = [];
        $data = [];

        for ($offset = 0; $offset < $months; $offset++) {
            $month = $start->addMonths($offset);

            $labels[] = $month->format('M Y');
            $data[] = (int) ($counts[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
