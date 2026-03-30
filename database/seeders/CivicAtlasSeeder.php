<?php

namespace Database\Seeders;

use App\Models\AdministrativeArea;
use App\Models\Dataset;
use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\ServiceMetric;
use App\Models\User;
use App\Services\SpatialColumnSynchronizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CivicAtlasSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@civicatlas.test')->firstOrFail();
        $spatial = app(SpatialColumnSynchronizer::class);

        $dataset = Dataset::query()->updateOrCreate(
            ['slug' => 'kp-open-data-demo'],
            [
                'name' => 'KP Open Data Demo Pack',
                'description' => 'A seeded demonstration dataset representing schools, health facilities, and civic services for the Peshawar atlas.',
                'source_type' => 'demo',
                'source_url' => 'https://www.opendata.kp.gov.pk/',
                'license' => 'Open Data - verify per source before production use.',
                'cadence' => 'demo',
                'owner_name' => 'Peshawar Civic GIS Atlas',
                'is_active' => true,
            ]
        );

        $datasetVersion = $dataset->versions()->updateOrCreate(
            ['version_label' => 'seed-2026-03'],
            [
                'source_year' => 2025,
                'status' => 'published',
                'file_disk' => 'local',
                'file_path' => 'seeds/demo.csv',
                'original_filename' => 'demo-seed.csv',
                'imported_rows' => 6,
                'published_at' => now(),
                'uploaded_by' => $admin->id,
            ]
        );

        $area = AdministrativeArea::query()->updateOrCreate(
            ['slug' => 'peshawar-district'],
            [
                'name' => 'Peshawar District',
                'code' => 'PK-KP-PEW',
                'level' => 'district',
                'center_latitude' => 34.0151,
                'center_longitude' => 71.5249,
                'boundary_geojson' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [71.3650, 34.1140],
                        [71.7030, 34.1140],
                        [71.7030, 33.8350],
                        [71.3650, 33.8350],
                        [71.3650, 34.1140],
                    ]],
                ],
                'meta' => [
                    'seed_note' => 'Demo bounding polygon for presentation use. Replace with authoritative boundary data before production.',
                ],
            ]
        );

        $spatial->syncAdministrativeArea($area);

        $categories = collect([
            ['slug' => 'education', 'name' => 'Education', 'icon' => 'school', 'color' => '#0f766e'],
            ['slug' => 'health', 'name' => 'Health', 'icon' => 'hospital', 'color' => '#dc2626'],
            ['slug' => 'civic-services', 'name' => 'Civic Services', 'icon' => 'city-hall', 'color' => '#2563eb'],
        ])->mapWithKeys(function (array $category) {
            $model = FacilityCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['name'].' locations in Peshawar.',
                    'icon' => $category['icon'],
                    'color' => $category['color'],
                    'is_active' => true,
                ]
            );

            return [$category['slug'] => $model];
        });

        ServiceMetric::query()->where('administrative_area_id', $area->id)->delete();

        collect([
            ['key' => 'population_estimate', 'label' => 'Population Estimate', 'value' => 4300000, 'unit' => 'people'],
            ['key' => 'school_density', 'label' => 'Schools per 100k residents', 'value' => 28.4, 'unit' => 'per 100k'],
            ['key' => 'health_sites', 'label' => 'Mapped Health Sites', 'value' => 24, 'unit' => 'facilities'],
        ])->each(function (array $metric) use ($area, $datasetVersion): void {
            ServiceMetric::query()->create([
                'administrative_area_id' => $area->id,
                'dataset_version_id' => $datasetVersion->id,
                'metric_key' => $metric['key'],
                'metric_label' => $metric['label'],
                'metric_value' => $metric['value'],
                'unit' => $metric['unit'],
            ]);
        });

        Facility::query()->where('dataset_version_id', $datasetVersion->id)->delete();

        collect([
            ['name' => 'Government Higher Secondary School No. 1', 'category' => 'education', 'type' => 'Public School', 'lat' => 34.0081, 'lng' => 71.5785, 'address' => 'University Road', 'locality' => 'Peshawar City'],
            ['name' => 'University of Peshawar Campus Hub', 'category' => 'education', 'type' => 'University', 'lat' => 34.0094, 'lng' => 71.4878, 'address' => 'Old Jamrud Road', 'locality' => 'University Town'],
            ['name' => 'Lady Reading Hospital', 'category' => 'health', 'type' => 'Tertiary Hospital', 'lat' => 34.0110, 'lng' => 71.5657, 'address' => 'GT Road', 'locality' => 'Peshawar Saddar'],
            ['name' => 'Hayatabad Medical Complex', 'category' => 'health', 'type' => 'Medical Complex', 'lat' => 33.9923, 'lng' => 71.4293, 'address' => 'Phase IV Hayatabad', 'locality' => 'Hayatabad'],
            ['name' => 'Peshawar Development Authority Facilitation Desk', 'category' => 'civic-services', 'type' => 'Government Office', 'lat' => 34.0016, 'lng' => 71.5380, 'address' => 'Khyber Road', 'locality' => 'Cantt'],
            ['name' => 'Tehsil Municipal Administration Service Center', 'category' => 'civic-services', 'type' => 'Municipal Service Center', 'lat' => 34.0047, 'lng' => 71.5584, 'address' => 'Hashtnagri', 'locality' => 'Inner City'],
        ])->each(function (array $facilitySeed) use ($categories, $datasetVersion, $area, $spatial): void {
            $facility = Facility::query()->create([
                'facility_category_id' => $categories[$facilitySeed['category']]->id,
                'dataset_version_id' => $datasetVersion->id,
                'administrative_area_id' => $area->id,
                'source_identifier' => Str::slug($facilitySeed['name']),
                'name' => $facilitySeed['name'],
                'slug' => Str::slug($facilitySeed['name']),
                'facility_type' => $facilitySeed['type'],
                'address_line' => $facilitySeed['address'],
                'locality' => $facilitySeed['locality'],
                'latitude' => $facilitySeed['lat'],
                'longitude' => $facilitySeed['lng'],
                'publication_status' => 'published',
                'provenance' => [
                    'dataset' => $datasetVersion->dataset->name,
                    'version' => $datasetVersion->version_label,
                    'seeded' => true,
                ],
                'meta' => [
                    'seed_note' => 'Demo facility for the portfolio atlas.',
                ],
            ]);

            $spatial->syncFacility($facility);
        });
    }
}
