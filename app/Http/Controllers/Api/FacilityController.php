<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Services\MapLayerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function __construct(
        private readonly MapLayerService $mapLayerService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $facilities = Facility::query()
            ->published()
            ->with(['category', 'administrativeArea', 'datasetVersion.dataset'])
            ->search($request->string('search')->toString())
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $request->string('category')->toString()));
            })
            ->when($request->filled('area'), fn ($query) => $query->whereHas('administrativeArea', fn ($areaQuery) => $areaQuery->where('slug', $request->string('area')->toString())))
            ->when($request->filled('source_year'), fn ($query) => $query->whereHas('datasetVersion', fn ($versionQuery) => $versionQuery->where('source_year', $request->integer('source_year'))))
            ->orderBy('name')
            ->limit(min($request->integer('limit', 150), 500))
            ->get();

        return response()->json([
            'filters' => $request->only(['search', 'category', 'area', 'source_year']),
            'count' => $facilities->count(),
            'geojson' => $this->mapLayerService->toFeatureCollection($facilities),
            'data' => $facilities->map(fn (Facility $facility) => [
                'id' => $facility->id,
                'slug' => $facility->slug,
                'name' => $facility->name,
                'category' => $facility->category?->name,
                'category_slug' => $facility->category?->slug,
                'facility_type' => $facility->facility_type,
                'address_line' => $facility->address_line,
                'locality' => $facility->locality,
                'latitude' => $facility->latitude,
                'longitude' => $facility->longitude,
                'detail_url' => route('facilities.show', $facility),
            ]),
        ]);
    }

    public function show(Facility $facility): JsonResponse
    {
        $facility->load(['category', 'datasetVersion.dataset', 'administrativeArea']);

        return response()->json([
            'facility' => [
                'id' => $facility->id,
                'slug' => $facility->slug,
                'name' => $facility->name,
                'category' => $facility->category?->name,
                'facility_type' => $facility->facility_type,
                'address_line' => $facility->address_line,
                'locality' => $facility->locality,
                'coordinates' => [
                    'lat' => $facility->latitude,
                    'lng' => $facility->longitude,
                ],
                'publication_status' => $facility->publication_status,
                'provenance' => $facility->provenance,
                'dataset' => $facility->datasetVersion?->dataset?->name,
                'source_year' => $facility->datasetVersion?->source_year,
                'administrative_area' => $facility->administrativeArea?->name,
            ],
        ]);
    }
}
