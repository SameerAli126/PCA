<x-app-layout :title="$facility->name" :seo="$seo ?? []">

    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="space-y-3">
                <p class="atlas-eyebrow">Facility detail</p>
                <h1 class="font-display text-4xl font-semibold text-slate-950">{{ $facility->name }}</h1>
                <p class="max-w-2xl text-base leading-7 text-slate-600">
                    {{ $facility->facility_type }} in {{ $facility->locality ?: 'Peshawar' }}, sourced from
                    {{ $facility->datasetVersion?->dataset?->name ?? 'the civic atlas pipeline' }}.
                </p>
            </div>
            <a class="atlas-button atlas-button-secondary" href="{{ route('atlas.explore') }}">Back to explorer</a>
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

    <section class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
            <div class="atlas-card space-y-6">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full px-4 py-2 text-sm font-semibold text-white" style="background-color: {{ $facility->category?->color ?? '#0f766e' }}">
                        {{ $facility->category?->name ?? 'Facility' }}
                    </span>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600">
                        {{ $facility->publication_status }}
                    </span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="atlas-eyebrow">Address</p>
                        <p class="mt-3 text-sm leading-6 text-slate-700">{{ $facility->address_line ?: 'Address unavailable' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="atlas-eyebrow">Coordinates</p>
                        <p class="mt-3 text-sm leading-6 text-slate-700">{{ $facility->latitude }}, {{ $facility->longitude }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="atlas-eyebrow">Area</p>
                        <p class="mt-3 text-sm leading-6 text-slate-700">{{ $facility->administrativeArea?->name ?? 'Peshawar' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="atlas-eyebrow">Dataset</p>
                        <p class="mt-3 text-sm leading-6 text-slate-700">
                            {{ $facility->datasetVersion?->dataset?->name ?? 'Seeded demo dataset' }}
                            @if ($facility->datasetVersion?->version_label)
                                · {{ $facility->datasetVersion->version_label }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5">
                    <p class="atlas-eyebrow">Provenance</p>
                    <pre class="mt-4 overflow-auto text-xs leading-6 text-slate-700">{{ json_encode($facility->provenance, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>

            <div class="atlas-card">
                <p class="atlas-eyebrow">Location context</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Facility map</h2>
                <div class="mt-5" id="facility-map"></div>
            </div>
        </div>

        <div class="atlas-card">
            <p class="atlas-eyebrow">Nearby facilities</p>
            <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Same-area context</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @forelse ($nearbyFacilities as $nearby)
                    <a class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300 hover:bg-white" href="{{ route('facilities.show', $nearby) }}">
                        <p class="text-sm font-semibold text-slate-900">{{ $nearby->name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $nearby->facility_type }} · {{ $nearby->locality }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No nearby facilities are currently seeded for this area.</p>
                @endforelse
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            const facility = {{ Illuminate\Support\Js::from([
                'name' => $facility->name,
                'lat' => $facility->latitude,
                'lng' => $facility->longitude,
                'category' => $facility->category?->name,
                'color' => $facility->category?->color,
            ]) }};

            const map = L.map('facility-map', { scrollWheelZoom: false }).setView([facility.lat, facility.lng], 14);
            L.tileLayer('{{ config('civic_atlas.map.tile_url') }}', {
                attribution: `{!! config('civic_atlas.map.tile_attribution') !!}`,
            }).addTo(map);

            L.circleMarker([facility.lat, facility.lng], {
                radius: 9,
                color: facility.color ?? '#0f766e',
                fillColor: facility.color ?? '#0f766e',
                fillOpacity: 0.9,
                weight: 1.5,
            }).bindPopup(`<strong>${facility.name}</strong><br>${facility.category ?? 'Facility'}`).addTo(map).openPopup();
        </script>
    @endpush
</x-app-layout>
