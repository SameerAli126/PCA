<x-app-layout>
    @php($title = 'Import Run')

    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="atlas-eyebrow">Import run</p>
                <h1 class="font-display text-4xl font-semibold text-slate-950">{{ $importRun->datasetVersion?->dataset?->name ?? 'Dataset import' }}</h1>
                <p class="mt-2 text-base text-slate-600">Version {{ $importRun->datasetVersion?->version_label }} · status: {{ $importRun->status }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.imports.validate', $importRun) }}">
                    @csrf
                    <button class="atlas-button atlas-button-secondary" type="submit">Validate import</button>
                </form>
                <form method="POST" action="{{ route('admin.imports.publish', $importRun) }}">
                    @csrf
                    <button class="atlas-button atlas-button-primary" type="submit">Publish import</button>
                </form>
            </div>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="atlas-card">
                <p class="atlas-eyebrow">Rows detected</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $importRun->stats['row_count'] ?? 0 }}</p>
            </div>
            <div class="atlas-card">
                <p class="atlas-eyebrow">Published rows</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $importRun->stats['published_rows'] ?? 0 }}</p>
            </div>
            <div class="atlas-card">
                <p class="atlas-eyebrow">Warnings</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $importRun->stats['warning_count'] ?? 0 }}</p>
            </div>
            <div class="atlas-card">
                <p class="atlas-eyebrow">Errors</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $importRun->stats['error_count'] ?? 0 }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[0.92fr_1.08fr]">
            <div class="atlas-card">
                <p class="atlas-eyebrow">Detected mapping</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Column assumptions</h2>
                <div class="mt-6 space-y-3">
                    @foreach (($importRun->mapping ?? []) as $field => $column)
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <span class="font-medium capitalize text-slate-700">{{ str_replace('_', ' ', $field) }}</span>
                            <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-white">{{ $column ?: 'missing' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="atlas-card">
                <p class="atlas-eyebrow">Preview rows</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Detected sample data</h2>
                <div class="mt-6 overflow-hidden rounded-[1.75rem] border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                            <tr>
                                @foreach (($importRun->stats['headers'] ?? []) as $header)
                                    <th class="px-4 py-3 font-medium">{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach (($importRun->stats['preview_rows'] ?? []) as $row)
                                <tr>
                                    @foreach (($importRun->stats['headers'] ?? []) as $header)
                                        <td class="px-4 py-3 text-slate-600">{{ $row[$header] ?? '' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="atlas-card">
            <p class="atlas-eyebrow">Validation issues</p>
            <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Error and warning stream</h2>
            <div class="mt-6 space-y-3">
                @forelse ($importRun->errors as $error)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <div class="flex items-center justify-between gap-4">
                            <p class="font-semibold text-slate-900">{{ $error->message }}</p>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] {{ $error->severity === 'error' ? 'bg-rose-600 text-white' : 'bg-amber-400 text-slate-950' }}">
                                {{ $error->severity }}
                            </span>
                        </div>
                        @if ($error->row_number)
                            <p class="mt-2 text-sm text-slate-500">Row {{ $error->row_number }}{{ $error->field_name ? ' · '.$error->field_name : '' }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No validation issues recorded yet.</p>
                @endforelse
            </div>
        </div>
    </section>
</x-app-layout>
