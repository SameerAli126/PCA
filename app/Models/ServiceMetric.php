<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'administrative_area_id',
        'dataset_version_id',
        'metric_key',
        'metric_label',
        'metric_value',
        'unit',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'metric_value' => 'float',
            'meta' => 'array',
        ];
    }

    public function administrativeArea(): BelongsTo
    {
        return $this->belongsTo(AdministrativeArea::class);
    }

    public function datasetVersion(): BelongsTo
    {
        return $this->belongsTo(DatasetVersion::class);
    }
}
