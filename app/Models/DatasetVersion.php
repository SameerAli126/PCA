<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatasetVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'dataset_id',
        'version_label',
        'source_year',
        'status',
        'file_disk',
        'file_path',
        'original_filename',
        'imported_rows',
        'published_at',
        'uploaded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'source_year' => 'integer',
            'imported_rows' => 'integer',
            'published_at' => 'datetime',
            'notes' => 'array',
        ];
    }

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function importRuns(): HasMany
    {
        return $this->hasMany(ImportRun::class);
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }
}
