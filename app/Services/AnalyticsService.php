<?php

namespace App\Services;

use App\Models\AdministrativeArea;
use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\ServiceMetric;

class AnalyticsService
{
    public function summary(?AdministrativeArea $area = null): array
    {
        $facilityQuery = Facility::query()->published()->with('category');

        if ($area) {
            $facilityQuery->where('administrative_area_id', $area->id);
        }

        $facilities = $facilityQuery->get();
        $categoryBreakdown = $facilities
            ->groupBy(fn (Facility $facility) => $facility->category?->name ?? 'Uncategorized')
            ->map(fn ($group, $label) => [
                'label' => $label,
                'count' => $group->count(),
            ])
            ->values()
            ->sortByDesc('count')
            ->values();

        $metrics = ServiceMetric::query()
            ->when($area, fn ($query) => $query->where('administrative_area_id', $area->id))
            ->orderBy('metric_label')
            ->get()
            ->map(fn (ServiceMetric $metric) => [
                'metric_key' => $metric->metric_key,
                'metric_label' => $metric->metric_label,
                'metric_value' => $metric->metric_value,
                'unit' => $metric->unit,
            ]);

        return [
            'total_facilities' => $facilities->count(),
            'categories' => FacilityCategory::query()->active()->count(),
            'areas' => AdministrativeArea::query()->count(),
            'category_breakdown' => $categoryBreakdown,
            'service_metrics' => $metrics,
        ];
    }
}
