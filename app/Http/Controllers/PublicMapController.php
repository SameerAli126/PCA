<?php

namespace App\Http\Controllers;

use App\Models\AdministrativeArea;
use App\Models\Dataset;
use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Services\AnalyticsService;
use App\Services\MapLayerService;
use Illuminate\View\View;

class PublicMapController extends Controller
{
    public function __construct(
        private readonly MapLayerService $mapLayerService,
        private readonly AnalyticsService $analyticsService,
    ) {
    }

    public function index(): View
    {
        $defaultArea = AdministrativeArea::query()
            ->where('slug', config('civic_atlas.default_area_slug'))
            ->first();

        $description = 'Explore schools, hospitals, and civic service points across Peshawar, with searchable facilities, map filters, and dataset provenance.';

        return view('atlas.index', [
            'atlasName' => config('civic_atlas.name'),
            'defaultArea' => $defaultArea,
            'summary' => $this->analyticsService->summary($defaultArea),
            'layers' => $this->mapLayerService->layers(),
            'categories' => FacilityCategory::query()->active()->orderBy('name')->get(),
            'featuredFacilities' => Facility::query()
                ->published()
                ->with(['category', 'administrativeArea'])
                ->latest()
                ->take(6)
                ->get(),
            'datasets' => Dataset::query()->where('is_active', true)->with('versions')->get(),
            'seo' => [
                'title' => config('civic_atlas.name'),
                'description' => $description,
                'canonical' => config('civic_atlas.public_url'),
                'type' => 'website',
                'json_ld' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => config('civic_atlas.name'),
                    'url' => rtrim((string) config('civic_atlas.public_url'), '/'),
                    'description' => $description,
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => config('civic_atlas.seo.public_owner'),
                    ],
                ],
            ],
        ]);
    }

    public function show(Facility $facility): View
    {
        $facility->load(['category', 'datasetVersion.dataset', 'administrativeArea']);

        $description = trim(sprintf(
            '%s in %s, Peshawar. Category: %s. Source: %s.',
            $facility->facility_type ?: 'Public facility',
            $facility->locality ?: 'Peshawar',
            $facility->category?->name ?? 'Facility',
            $facility->datasetVersion?->dataset?->name ?? 'Civic atlas pipeline'
        ));

        $nearbyFacilities = Facility::query()
            ->published()
            ->whereKeyNot($facility->id)
            ->when($facility->administrative_area_id, fn ($query) => $query->where('administrative_area_id', $facility->administrative_area_id))
            ->with('category')
            ->take(4)
            ->get();

        return view('facilities.show', [
            'facility' => $facility,
            'nearbyFacilities' => $nearbyFacilities,
            'seo' => [
                'title' => $facility->name,
                'description' => $description,
                'canonical' => rtrim((string) config('civic_atlas.public_url'), '/').route('facilities.show', $facility, false),
                'type' => 'website',
                'json_ld' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Place',
                    'name' => $facility->name,
                    'description' => $description,
                    'geo' => [
                        '@type' => 'GeoCoordinates',
                        'latitude' => $facility->latitude,
                        'longitude' => $facility->longitude,
                    ],
                    'containedInPlace' => $facility->administrativeArea?->name,
                    'url' => rtrim((string) config('civic_atlas.public_url'), '/').route('facilities.show', $facility, false),
                ],
            ],
        ]);
    }
}
