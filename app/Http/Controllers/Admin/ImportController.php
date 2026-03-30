<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Models\ImportRun;
use App\Services\Imports\AtlasImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function __construct(
        private readonly AtlasImportService $atlasImportService,
    ) {
    }

    public function index(): View
    {
        return view('admin.imports.index', [
            'datasets' => Dataset::query()->where('is_active', true)->orderBy('name')->get(),
            'imports' => ImportRun::query()->with('datasetVersion.dataset', 'initiatedBy')->latest()->paginate(12),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dataset_id' => ['required', 'exists:datasets,id'],
            'source_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'import_file' => ['required', 'file', 'mimes:csv,xls,xlsx'],
        ]);

        $dataset = Dataset::query()->findOrFail($validated['dataset_id']);
        $importRun = $this->atlasImportService->createDraft(
            $dataset,
            $request->file('import_file'),
            $request->user(),
            $validated['source_year'] ?? null,
        );

        return redirect()
            ->route('admin.imports.show', $importRun)
            ->with('status', 'Import uploaded. Review the detected mapping and run validation.');
    }

    public function show(ImportRun $importRun): View
    {
        return view('admin.imports.show', [
            'importRun' => $importRun->load('datasetVersion.dataset', 'errors', 'initiatedBy'),
        ]);
    }

    public function validateImport(ImportRun $importRun): RedirectResponse
    {
        $this->atlasImportService->validate($importRun);

        return redirect()
            ->route('admin.imports.show', $importRun)
            ->with('status', 'Validation finished. Resolve any blocking issues before publishing.');
    }

    public function publish(Request $request, ImportRun $importRun): RedirectResponse
    {
        $this->atlasImportService->publish($importRun, $request->user());

        return redirect()
            ->route('admin.imports.show', $importRun)
            ->with('status', 'Import published. Facilities are now visible on the atlas.');
    }
}
