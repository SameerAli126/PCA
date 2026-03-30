<?php

namespace App\Services\Imports;

use App\Models\AdministrativeArea;
use App\Models\AuditLog;
use App\Models\Dataset;
use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\ImportRun;
use App\Models\User;
use App\Services\SpatialColumnSynchronizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class AtlasImportService
{
    public function __construct(
        private readonly SpatialColumnSynchronizer $spatialColumnSynchronizer,
    ) {
    }

    public function createDraft(Dataset $dataset, UploadedFile $file, User $user, ?int $sourceYear = null): ImportRun
    {
        $version = $dataset->versions()->create([
            'version_label' => now()->format('Y.m.d-His'),
            'source_year' => $sourceYear,
            'status' => 'draft',
            'file_disk' => 'local',
            'file_path' => $file->storeAs(
                sprintf('imports/%s', $dataset->slug),
                Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'-'.now()->format('His').'.'.$file->getClientOriginalExtension(),
                'local'
            ),
            'original_filename' => $file->getClientOriginalName(),
            'uploaded_by' => $user->id,
        ]);

        ['headers' => $headers, 'rows' => $rows] = $this->extractRows($version->file_disk, $version->file_path);
        $mapping = $this->detectMapping($headers);

        $importRun = $version->importRuns()->create([
            'initiated_by' => $user->id,
            'status' => 'uploaded',
            'mapping' => $mapping,
            'stats' => [
                'headers' => $headers,
                'preview_rows' => array_slice($rows, 0, config('civic_atlas.dataset.preview_rows')),
                'row_count' => count($rows),
            ],
            'started_at' => now(),
        ]);

        AuditLog::query()->create([
            'user_id' => $user->id,
            'action' => 'import.created',
            'auditable_type' => ImportRun::class,
            'auditable_id' => $importRun->id,
            'after' => [
                'dataset' => $dataset->name,
                'version' => $version->version_label,
            ],
            'context' => ['file' => $version->original_filename],
        ]);

        return $importRun->load('datasetVersion.dataset', 'initiatedBy');
    }

    public function validate(ImportRun $importRun): ImportRun
    {
        $importRun->errors()->delete();

        ['headers' => $headers, 'rows' => $rows] = $this->extractRows(
            $importRun->datasetVersion->file_disk,
            $importRun->datasetVersion->file_path
        );

        $mapping = $importRun->mapping ?: $this->detectMapping($headers);
        $missingColumns = collect(config('civic_atlas.dataset.required_columns'))
            ->filter(fn (string $column) => empty($mapping[$column]))
            ->values();

        foreach ($missingColumns as $missingColumn) {
            $importRun->errors()->create([
                'field_name' => $missingColumn,
                'message' => "Missing required column mapping for {$missingColumn}.",
                'severity' => 'error',
            ]);
        }

        foreach (array_slice($rows, 0, 10) as $index => $row) {
            foreach (['name', 'latitude', 'longitude'] as $requiredField) {
                $sourceColumn = $mapping[$requiredField] ?? null;

                if ($sourceColumn && blank($row[$sourceColumn] ?? null)) {
                    $importRun->errors()->create([
                        'row_number' => $index + 2,
                        'field_name' => $requiredField,
                        'message' => "Sample row is missing {$requiredField}.",
                        'payload' => $row,
                        'severity' => 'warning',
                    ]);
                }
            }
        }

        $hasBlockingErrors = $importRun->errors()->where('severity', 'error')->exists();

        $importRun->update([
            'status' => $hasBlockingErrors ? 'failed' : 'validated',
            'mapping' => $mapping,
            'stats' => array_merge($importRun->stats ?? [], [
                'headers' => $headers,
                'row_count' => count($rows),
                'preview_rows' => array_slice($rows, 0, config('civic_atlas.dataset.preview_rows')),
                'warning_count' => $importRun->errors()->where('severity', 'warning')->count(),
                'error_count' => $importRun->errors()->where('severity', 'error')->count(),
            ]),
            'finished_at' => now(),
            'error_summary' => $hasBlockingErrors ? 'Validation failed. Fix the missing columns and try again.' : null,
        ]);

        return $importRun->fresh(['errors', 'datasetVersion.dataset']);
    }

    public function publish(ImportRun $importRun, User $user): ImportRun
    {
        if ($importRun->status !== 'validated') {
            $importRun = $this->validate($importRun);
        }

        if ($importRun->status !== 'validated') {
            throw new RuntimeException('Import must be validated before it can be published.');
        }

        ['rows' => $rows] = $this->extractRows($importRun->datasetVersion->file_disk, $importRun->datasetVersion->file_path);
        $mapping = $importRun->mapping ?? [];
        $palette = config('civic_atlas.palette');
        $area = AdministrativeArea::query()->where('slug', config('civic_atlas.default_area_slug'))->first();

        DB::transaction(function () use ($importRun, $rows, $mapping, $palette, $area, $user): void {
            Facility::query()->where('dataset_version_id', $importRun->dataset_version_id)->delete();
            $importRun->errors()->where('severity', 'warning')->delete();

            $published = 0;
            $skipped = 0;

            foreach ($rows as $index => $row) {
                $name = trim((string) ($row[$mapping['name']] ?? ''));
                $categoryName = trim((string) ($row[$mapping['category']] ?? 'General Facility'));
                $latitude = $this->normalizeCoordinate($row[$mapping['latitude']] ?? null);
                $longitude = $this->normalizeCoordinate($row[$mapping['longitude']] ?? null);

                if ($name === '' || $latitude === null || $longitude === null) {
                    $skipped++;
                    $importRun->errors()->create([
                        'row_number' => $index + 2,
                        'message' => 'Skipped row because name/latitude/longitude was invalid.',
                        'payload' => $row,
                        'severity' => 'warning',
                    ]);
                    continue;
                }

                $category = FacilityCategory::query()->firstOrCreate(
                    ['slug' => Str::slug($categoryName)],
                    [
                        'name' => $categoryName,
                        'description' => 'Imported from a civic dataset.',
                        'icon' => 'marker',
                        'color' => $palette[array_rand($palette)],
                        'is_active' => true,
                    ]
                );

                $facility = Facility::query()->create([
                    'facility_category_id' => $category->id,
                    'dataset_version_id' => $importRun->dataset_version_id,
                    'administrative_area_id' => $area?->id,
                    'source_identifier' => (string) ($row[$mapping['source_identifier']] ?? Str::uuid()),
                    'name' => $name,
                    'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
                    'facility_type' => trim((string) ($row[$mapping['facility_type']] ?? $categoryName)),
                    'address_line' => trim((string) ($row[$mapping['address_line']] ?? '')),
                    'locality' => trim((string) ($row[$mapping['locality']] ?? 'Peshawar')),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'publication_status' => 'published',
                    'provenance' => [
                        'dataset' => $importRun->datasetVersion->dataset->name,
                        'version' => $importRun->datasetVersion->version_label,
                        'source_year' => $importRun->datasetVersion->source_year,
                    ],
                    'meta' => ['raw' => $row],
                ]);

                $this->spatialColumnSynchronizer->syncFacility($facility);
                $published++;
            }

            $importRun->datasetVersion->update([
                'status' => 'published',
                'published_at' => now(),
                'imported_rows' => $published,
            ]);

            $importRun->update([
                'status' => 'published',
                'finished_at' => now(),
                'stats' => array_merge($importRun->stats ?? [], [
                    'published_rows' => $published,
                    'skipped_rows' => $skipped,
                ]),
            ]);

            AuditLog::query()->create([
                'user_id' => $user->id,
                'action' => 'import.published',
                'auditable_type' => ImportRun::class,
                'auditable_id' => $importRun->id,
                'after' => [
                    'published_rows' => $published,
                    'skipped_rows' => $skipped,
                ],
                'context' => ['dataset_version_id' => $importRun->dataset_version_id],
            ]);
        });

        return $importRun->fresh(['errors', 'datasetVersion.dataset']);
    }

    private function extractRows(string $disk, string $path): array
    {
        $absolutePath = Storage::disk($disk)->path($path);
        $spreadsheet = IOFactory::load($absolutePath);
        $sheetRows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if (count($sheetRows) < 2) {
            return ['headers' => [], 'rows' => []];
        }

        $rawHeaders = array_shift($sheetRows);
        $headers = [];

        foreach ($rawHeaders as $index => $header) {
            $normalized = $this->normalizeHeader((string) $header);
            $headers[$index] = $normalized !== '' ? $normalized : 'column_'.($index + 1);
        }

        $rows = [];

        foreach ($sheetRows as $row) {
            $assoc = [];

            foreach ($headers as $index => $header) {
                $assoc[$header] = is_string($row[$index] ?? null)
                    ? trim($row[$index])
                    : $row[$index];
            }

            if (collect($assoc)->filter(fn ($value) => ! blank($value))->isNotEmpty()) {
                $rows[] = $assoc;
            }
        }

        return ['headers' => array_values($headers), 'rows' => $rows];
    }

    private function detectMapping(array $headers): array
    {
        $aliases = [
            'name' => ['name', 'facility_name', 'school_name', 'hospital_name'],
            'category' => ['category', 'sector', 'department', 'group'],
            'facility_type' => ['facility_type', 'type', 'school_type', 'level'],
            'latitude' => ['latitude', 'lat', 'y'],
            'longitude' => ['longitude', 'lng', 'lon', 'x'],
            'address_line' => ['address', 'address_line', 'location'],
            'locality' => ['locality', 'town', 'tehsil', 'district', 'uc'],
            'source_identifier' => ['source_identifier', 'source_id', 'emis_code', 'facility_id', 'id'],
        ];

        $mapping = [];

        foreach ($aliases as $field => $options) {
            $mapping[$field] = collect($options)->first(fn ($option) => in_array($option, $headers, true));
        }

        return $mapping;
    }

    private function normalizeHeader(string $header): string
    {
        return (string) Str::of($header)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_');
    }

    private function normalizeCoordinate(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
