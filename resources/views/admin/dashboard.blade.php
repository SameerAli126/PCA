<x-app-layout>
    @php($title = 'Admin Dashboard')

    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="atlas-eyebrow">Operator console</p>
                <h1 class="font-display text-4xl font-semibold text-slate-950">Admin dashboard</h1>
            </div>
            <div class="flex flex-wrap gap-3">
                <a class="atlas-button atlas-button-secondary" href="{{ route('admin.datasets.index') }}">Datasets</a>
                <a class="atlas-button atlas-button-primary" href="{{ route('admin.imports.index') }}">Imports</a>
            </div>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="atlas-card">
                <p class="atlas-eyebrow">Facilities</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary['total_facilities'] }}</p>
            </div>
            <div class="atlas-card">
                <p class="atlas-eyebrow">Categories</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary['categories'] }}</p>
            </div>
            <div class="atlas-card">
                <p class="atlas-eyebrow">Areas</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary['areas'] }}</p>
            </div>
            <div class="atlas-card">
                <p class="atlas-eyebrow">Recent imports</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $imports->count() }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="atlas-card">
                <p class="atlas-eyebrow">Datasets</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Current source catalog</h2>
                <div class="mt-6 space-y-3">
                    @foreach ($datasets as $dataset)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $dataset->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $dataset->source_type }} · {{ $dataset->versions->count() }} versions</p>
                                </div>
                                <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">{{ $dataset->is_active ? 'active' : 'inactive' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="atlas-card">
                <p class="atlas-eyebrow">Imports</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Recent pipeline activity</h2>
                <div class="mt-6 space-y-3">
                    @foreach ($imports as $import)
                        <a class="block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-slate-300 hover:bg-white" href="{{ route('admin.imports.show', $import) }}">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $import->datasetVersion?->dataset?->name ?? 'Dataset import' }}</p>
                                    <p class="text-sm text-slate-500">{{ $import->datasetVersion?->version_label }} · {{ $import->initiatedBy?->name }}</p>
                                </div>
                                <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-white">{{ $import->status }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
