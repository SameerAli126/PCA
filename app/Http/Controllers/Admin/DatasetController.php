<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Dataset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DatasetController extends Controller
{
    public function index(): View
    {
        return view('admin.datasets.index', [
            'datasets' => Dataset::query()->withCount('versions')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'source_type' => ['required', 'in:official_kp,osm,manual,demo'],
            'source_url' => ['nullable', 'url'],
            'license' => ['nullable', 'string', 'max:255'],
            'cadence' => ['nullable', 'string', 'max:100'],
            'owner_name' => ['nullable', 'string', 'max:255'],
        ]);

        $dataset = Dataset::query()->create([
            ...$validated,
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(4)),
            'is_active' => true,
        ]);

        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => 'dataset.created',
            'auditable_type' => Dataset::class,
            'auditable_id' => $dataset->id,
            'after' => $dataset->only(['name', 'slug', 'source_type']),
        ]);

        return redirect()
            ->route('admin.datasets.index')
            ->with('status', 'Dataset created. You can now upload a version from the imports screen.');
    }
}
