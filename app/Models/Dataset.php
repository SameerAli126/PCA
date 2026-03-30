<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dataset extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'source_type',
        'source_url',
        'license',
        'cadence',
        'owner_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DatasetVersion::class);
    }

    public function latestPublishedVersion(): ?DatasetVersion
    {
        return $this->versions()
            ->where('status', 'published')
            ->latest('published_at')
            ->first();
    }
}
