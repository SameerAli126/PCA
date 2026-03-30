<x-app-layout :title="$atlasName.' Explorer'" :seo="$seo ?? []">
    <x-slot name="header">
        <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-end">
            <div class="space-y-4">
                <p class="atlas-eyebrow">Dedicated map explorer</p>
                <div class="space-y-3">
                    <h1 class="font-display text-4xl font-semibold text-slate-950 sm:text-5xl">
                        Explore the public atlas without the workbench crowding the map.
                    </h1>
                    <p class="max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                        This route is focused on spatial navigation. The workbench owns shortlist review, while this page gives the map
                        a proper stage for overlays, fullscreen, and feature inspection.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a class="atlas-button atlas-button-primary" href="#atlas-explorer">Open Explorer</a>
                    <a class="atlas-button atlas-button-secondary" href="{{ route('atlas.index', array_filter($activeFilters)) }}">Back to Workbench</a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="atlas-card">
                    <p class="atlas-eyebrow">Visible facilities</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950" id="atlas-count">{{ $featuredFacilities->count() }}</p>
                    <p class="mt-2 text-sm text-slate-500">Current shortlist loaded into the dedicated map view.</p>
                </div>
                <div class="atlas-card">
                    <p class="atlas-eyebrow">Area focus</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $defaultArea?->name ?? 'Peshawar' }}</p>
                    <p class="mt-2 text-sm text-slate-500">Default spatial focus for the current public atlas.</p>
                </div>
                <div class="atlas-card">
                    <p class="atlas-eyebrow">Interaction mode</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">Map-first</p>
                    <p class="mt-2 text-sm text-slate-500">Fullscreen, focus, overlay, and layer controls stay in view.</p>
                </div>
            </div>
        </div>
    </x-slot>

    @push('head')
        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""
        />
    @endpush

    <section class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8" id="atlas-explorer">
        <form class="hidden" id="atlas-filter-form">
            <input name="search" type="hidden" value="{{ $activeFilters['search'] }}" />
            <input name="category" type="hidden" value="{{ $activeFilters['category'] }}" />
            <input name="area" type="hidden" value="{{ $activeFilters['area'] }}" />
            <input name="source_year" type="hidden" value="{{ $activeFilters['source_year'] }}" />
            <button id="atlas-reset" type="button">Reset</button>
        </form>

        <div class="hidden" id="atlas-results"></div>

        @include('atlas.partials.map-panel')
    </section>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        @include('atlas.partials.map-script')
    @endpush
</x-app-layout>
