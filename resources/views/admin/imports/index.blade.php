<x-app-layout>
    @php($title = 'Imports')

    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="atlas-eyebrow">Pipeline control</p>
                <h1 class="font-display text-4xl font-semibold text-slate-950">Imports</h1>
            </div>
            <a class="atlas-button atlas-button-secondary" href="{{ route('admin.datasets.index') }}">Manage datasets</a>
        </div>
    </x-slot>

    <section class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
        <div class="atlas-card">
            <p class="atlas-eyebrow">Upload source file</p>
            <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Create a dataset version</h2>

            <form class="mt-6 space-y-4" method="POST" action="{{ route('admin.imports.store') }}" enctype="multipart/form-data">
                @csrf
                <label class="block space-y-2 text-sm font-medium text-slate-700">
                    Dataset
                    <select class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="dataset_id" required>
                        <option value="">Select a dataset</option>
                        @foreach ($datasets as $dataset)
                            <option value="{{ $dataset->id }}">{{ $dataset->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block space-y-2 text-sm font-medium text-slate-700">
                    Source year
                    <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="source_year" type="number" min="2000" max="2100" placeholder="2025" />
                </label>

                <label class="block space-y-2 text-sm font-medium text-slate-700">
                    Spreadsheet
                    <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-white" name="import_file" type="file" accept=".csv,.xls,.xlsx" required />
                </label>

                <button class="atlas-button atlas-button-primary" type="submit">Upload import</button>
            </form>
        </div>

        <div class="atlas-card">
            <p class="atlas-eyebrow">Import history</p>
            <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Recent import runs</h2>

            <div class="mt-6 space-y-3">
                @foreach ($imports as $import)
                    <a class="block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-slate-300 hover:bg-white" href="{{ route('admin.imports.show', $import) }}">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $import->datasetVersion?->dataset?->name ?? 'Dataset import' }}</p>
                                <p class="text-sm text-slate-500">{{ $import->datasetVersion?->version_label }} · {{ $import->initiatedBy?->name ?? 'system' }}</p>
                            </div>
                            <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-white">{{ $import->status }}</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $imports->links() }}
            </div>
        </div>
    </section>
</x-app-layout>
