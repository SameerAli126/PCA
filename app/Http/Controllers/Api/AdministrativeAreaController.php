<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeArea;
use App\Services\MapLayerService;
use Illuminate\Http\JsonResponse;

class AdministrativeAreaController extends Controller
{
    public function __construct(
        private readonly MapLayerService $mapLayerService,
    ) {
    }

    public function show(AdministrativeArea $administrativeArea): JsonResponse
    {
        $administrativeArea->load('serviceMetrics');

        return response()->json([
            'area' => [
                'slug' => $administrativeArea->slug,
                'name' => $administrativeArea->name,
                'level' => $administrativeArea->level,
                'center' => [
                    'lat' => $administrativeArea->center_latitude,
                    'lng' => $administrativeArea->center_longitude,
                ],
                'feature' => $this->mapLayerService->areaFeature($administrativeArea),
                'metrics' => $administrativeArea->serviceMetrics->map(fn ($metric) => [
                    'metric_key' => $metric->metric_key,
                    'metric_label' => $metric->metric_label,
                    'metric_value' => $metric->metric_value,
                    'unit' => $metric->unit,
                ]),
            ],
        ]);
    }
}
