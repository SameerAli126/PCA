<?php

namespace App\Services;

use App\Models\AdministrativeArea;
use App\Models\Facility;
use App\Models\FacilityCategory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class MapLayerService
{
    public function layers(): array
    {
        return [
            'base_map' => [
                'center' => config('civic_atlas.map.center'),
                'zoom' => config('civic_atlas.map.zoom'),
                'tile_url' => config('civic_atlas.map.tile_url'),
                'tile_attribution' => config('civic_atlas.map.tile_attribution'),
            ],
            'facility_layers' => FacilityCategory::query()
                ->active()
                ->withCount(['facilities' => fn ($query) => $query->published()])
                ->orderBy('name')
                ->get()
                ->map(fn (FacilityCategory $category) => [
                    'slug' => $category->slug,
                    'name' => $category->name,
                    'icon' => $category->icon,
                    'color' => $category->color,
                    'count' => $category->facilities_count,
                ]),
            'areas' => AdministrativeArea::query()
                ->orderBy('level')
                ->orderBy('name')
                ->get()
                ->map(fn (AdministrativeArea $area) => [
                    'slug' => $area->slug,
                    'name' => $area->name,
                    'level' => $area->level,
                    'center' => [
                        'lat' => $area->center_latitude,
                        'lng' => $area->center_longitude,
                    ],
                ]),
        ];
    }

    public function toFeatureCollection(EloquentCollection $facilities): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => $facilities
                ->filter(fn (Facility $facility) => $facility->latitude && $facility->longitude)
                ->map(fn (Facility $facility) => $this->toFeature($facility))
                ->values()
                ->all(),
        ];
    }

    public function areaFeature(AdministrativeArea $area): array
    {
        return [
            'type' => 'Feature',
            'properties' => [
                'slug' => $area->slug,
                'name' => $area->name,
                'level' => $area->level,
            ],
            'geometry' => $area->boundary_geojson,
        ];
    }

    private function toFeature(Facility $facility): array
    {
        return [
            'type' => 'Feature',
            'properties' => [
                'id' => $facility->id,
                'slug' => $facility->slug,
                'name' => $facility->name,
                'category' => $facility->category?->name,
                'category_slug' => $facility->category?->slug,
                'facility_type' => $facility->facility_type,
                'address_line' => $facility->address_line,
                'locality' => $facility->locality,
                'detail_url' => route('facilities.show', $facility),
                'color' => $facility->category?->color,
            ],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    (float) $facility->longitude,
                    (float) $facility->latitude,
                ],
            ],
        ];
    }
}
