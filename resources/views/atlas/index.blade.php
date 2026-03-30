<x-app-layout :title="$atlasName" :seo="$seo ?? []">
    @php
        $title = $atlasName;
        $sourceYears = $datasets->flatMap->versions->pluck('source_year')->filter()->unique()->sortDesc()->values();
    @endphp

    <x-slot name="header">
        <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr] lg:items-end">
            <div class="space-y-4">
                <p class="atlas-eyebrow">Portfolio-grade civic intelligence stack</p>
                <div class="space-y-3">
                    <h1 class="font-display text-4xl font-semibold text-slate-950 sm:text-5xl">
                        Peshawar public-services GIS, built for real data pipelines.
                    </h1>
                    <p class="max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                        Explore schools, hospitals, and civic service points across Peshawar, then step into the admin console to
                        ingest fresh datasets, validate rows, and publish new map layers with provenance intact.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a class="atlas-button atlas-button-primary" href="#atlas-workbench">Open Atlas</a>
                    @auth
                        @if (auth()->user()->canAccessAdmin())
                            <a class="atlas-button atlas-button-secondary" href="{{ route('admin.dashboard') }}">Open Admin Console</a>
                        @endif
                    @else
                        <a class="atlas-button atlas-button-secondary" href="{{ route('login') }}">Operator Login</a>
                    @endauth
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="atlas-card">
                    <p class="atlas-eyebrow">Mapped facilities</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary['total_facilities'] }}</p>
                    <p class="mt-2 text-sm text-slate-500">Published facilities visible on the public atlas.</p>
                </div>
                <div class="atlas-card">
                    <p class="atlas-eyebrow">Categories</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary['categories'] }}</p>
                    <p class="mt-2 text-sm text-slate-500">Education, health, and civic service layers.</p>
                </div>
                <div class="atlas-card">
                    <p class="atlas-eyebrow">Area scope</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary['areas'] }}</p>
                    <p class="mt-2 text-sm text-slate-500">Peshawar-first structure with KP-ready expansion.</p>
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

    <section class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8" id="atlas-workbench">
        <div class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
            <div class="space-y-6">
                <div class="atlas-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="atlas-eyebrow">Map filters</p>
                            <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Atlas workbench</h2>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-slate-600">
                            {{ $defaultArea?->name ?? 'Peshawar' }}
                        </span>
                    </div>

                    <form class="mt-6 space-y-4" id="atlas-filter-form">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="space-y-2 text-sm font-medium text-slate-700">
                                Search
                                <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" type="search" name="search" placeholder="Hospital, school, office..." />
                            </label>

                            <label class="space-y-2 text-sm font-medium text-slate-700">
                                Category
                                <select class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="category">
                                    <option value="">All categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->slug }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="space-y-2 text-sm font-medium text-slate-700">
                                Area
                                <select class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="area">
                                    <option value="">All mapped areas</option>
                                    @foreach ($layers['areas'] as $area)
                                        <option value="{{ $area['slug'] }}" @selected($defaultArea && $defaultArea->slug === $area['slug'])>{{ $area['name'] }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="space-y-2 text-sm font-medium text-slate-700">
                                Source year
                                <select class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="source_year">
                                    <option value="">All source years</option>
                                    @foreach ($sourceYears as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button class="atlas-button atlas-button-primary" type="submit">Apply filters</button>
                            <button class="atlas-button atlas-button-secondary" type="button" id="atlas-reset">Reset</button>
                        </div>
                    </form>
                </div>

                <div class="atlas-card">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="atlas-eyebrow">Live results</p>
                            <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Visible facilities</h2>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-slate-600" id="atlas-count">
                            {{ $featuredFacilities->count() }} seeded
                        </span>
                    </div>

                    <div class="mt-5 space-y-3" id="atlas-results">
                        @foreach ($featuredFacilities as $facility)
                            <a class="block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-slate-300 hover:bg-white" href="{{ route('facilities.show', $facility) }}">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $facility->name }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $facility->facility_type }} · {{ $facility->locality }}</p>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold text-white" style="background-color: {{ $facility->category?->color ?? '#0f766e' }}">
                                        {{ $facility->category?->name ?? 'Facility' }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="atlas-card">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="atlas-eyebrow">Public atlas</p>
                        <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Interactive facility map</h2>
                    </div>
                    <div class="text-sm text-slate-500">
                        Tiles from OpenStreetMap. Data provenance stays attached to every facility.
                    </div>
                </div>

                <div class="mt-5" id="atlas-map"></div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
            <div class="atlas-card">
                <p class="atlas-eyebrow">Category breakdown</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Portfolio snapshot</h2>
                <div class="mt-6 grid gap-3">
                    @foreach ($summary['category_breakdown'] as $category)
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <span class="font-medium text-slate-700">{{ $category['label'] }}</span>
                            <span class="text-lg font-semibold text-slate-950">{{ $category['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="atlas-card">
                <p class="atlas-eyebrow">Dataset feed</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Available sources</h2>
                <div class="mt-6 space-y-3">
                    @foreach ($datasets as $dataset)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $dataset->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $dataset->source_type }} · {{ $dataset->license ?? 'license pending' }}</p>
                                </div>
                                <a class="text-sm font-medium text-teal-700" href="{{ $dataset->source_url }}" target="_blank" rel="noreferrer">Source ↗</a>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $dataset->description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            const atlasLayers = {{ Illuminate\Support\Js::from($layers) }};
            const filterForm = document.getElementById('atlas-filter-form');
            const resultsContainer = document.getElementById('atlas-results');
            const countBadge = document.getElementById('atlas-count');
            const markerLayer = L.layerGroup();
            const map = L.map('atlas-map', {
                scrollWheelZoom: false,
            }).setView([atlasLayers.base_map.center.lat, atlasLayers.base_map.center.lng], atlasLayers.base_map.zoom);

            L.tileLayer(atlasLayers.base_map.tile_url, {
                attribution: atlasLayers.base_map.tile_attribution,
                maxZoom: 18,
            }).addTo(map);

            markerLayer.addTo(map);

            async function loadFacilities() {
                const params = new URLSearchParams(new FormData(filterForm));
                const response = await fetch(`/api/facilities?${params.toString()}`);
                const payload = await response.json();

                markerLayer.clearLayers();
                resultsContainer.innerHTML = '';
                countBadge.textContent = `${payload.count} visible`;

                payload.geojson.features.forEach((feature) => {
                    const marker = L.circleMarker(
                        [feature.geometry.coordinates[1], feature.geometry.coordinates[0]],
                        {
                            radius: 8,
                            color: feature.properties.color ?? '#0f766e',
                            fillColor: feature.properties.color ?? '#0f766e',
                            fillOpacity: 0.85,
                            weight: 1.5,
                        }
                    );

                    marker.bindPopup(`
                        <div class="space-y-1">
                            <strong>${feature.properties.name}</strong>
                            <div>${feature.properties.category ?? 'Facility'}</div>
                            <div>${feature.properties.facility_type ?? ''}</div>
                            <a href="${feature.properties.detail_url}" style="color:#0f766e;font-weight:600;">Open detail</a>
                        </div>
                    `);

                    markerLayer.addLayer(marker);
                });

                payload.data.slice(0, 8).forEach((facility) => {
                    const item = document.createElement('a');
                    item.href = facility.detail_url;
                    item.className = 'block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-slate-300 hover:bg-white';
                    item.innerHTML = `
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">${facility.name}</p>
                                <p class="mt-1 text-sm text-slate-500">${facility.facility_type ?? 'Facility'} · ${facility.locality ?? 'Peshawar'}</p>
                            </div>
                            <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">${facility.category ?? 'Facility'}</span>
                        </div>
                    `;
                    resultsContainer.appendChild(item);
                });
            }

            filterForm.addEventListener('submit', (event) => {
                event.preventDefault();
                loadFacilities();
            });

            document.getElementById('atlas-reset').addEventListener('click', () => {
                filterForm.reset();
                loadFacilities();
            });

            loadFacilities();
        </script>
    @endpush
</x-app-layout>
