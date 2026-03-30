<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_category_id',
        'dataset_version_id',
        'administrative_area_id',
        'source_identifier',
        'name',
        'slug',
        'facility_type',
        'address_line',
        'locality',
        'latitude',
        'longitude',
        'publication_status',
        'provenance',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'provenance' => 'array',
            'meta' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FacilityCategory::class, 'facility_category_id');
    }

    public function datasetVersion(): BelongsTo
    {
        return $this->belongsTo(DatasetVersion::class);
    }

    public function administrativeArea(): BelongsTo
    {
        return $this->belongsTo(AdministrativeArea::class);
    }

    public function scopePublished($query)
    {
        return $query->where('publication_status', 'published');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($nested) use ($term) {
            $nested->where('name', 'like', "%{$term}%")
                ->orWhere('facility_type', 'like', "%{$term}%")
                ->orWhere('locality', 'like', "%{$term}%")
                ->orWhere('address_line', 'like', "%{$term}%");
        });
    }
}
