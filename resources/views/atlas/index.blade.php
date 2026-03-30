<x-app-layout :title="$atlasName" :seo="$seo ?? []">
    @php
        $sourceYears = $datasets->flatMap->versions->pluck('source_year')->filter()->unique()->sortDesc()->values();
    @endphp

    <x-slot name="header">
        <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-end">
            <div class="space-y-4">
                <p class="atlas-eyebrow">Civic GIS workbench</p>
                <div class="space-y-3">
                    <h1 class="font-display text-4xl font-semibold text-slate-950 sm:text-5xl">
                        Review filters and shortlist facilities before opening the full map.
                    </h1>
                    <p class="max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                        Use the workbench to narrow the dataset, inspect the visible facility list, and then launch the dedicated
                        explorer with the same context once you are ready to navigate the map properly.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a class="atlas-button atlas-button-primary" href="#atlas-workbench">Open Workbench</a>
                    <a class="atlas-button atlas-button-secondary" href="{{ route('atlas.explore', array_filter($activeFilters)) }}">Open Full Map Explorer</a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="atlas-card">
                    <p class="atlas-eyebrow">Mapped facilities</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary['total_facilities'] }}</p>
                    <p class="mt-2 text-sm text-slate-500">Published facilities available for review and map exploration.</p>
                </div>
                <div class="atlas-card">
                    <p class="atlas-eyebrow">Categories</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary['categories'] }}</p>
                    <p class="mt-2 text-sm text-slate-500">Education, health, and civic service layers.</p>
                </div>
                <div class="atlas-card">
                    <p class="atlas-eyebrow">Explorer mode</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">1</p>
                    <p class="mt-2 text-sm text-slate-500">Dedicated full-map route with the clutter removed.</p>
                </div>
            </div>
        </div>
    </x-slot>

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
                                <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" type="search" name="search" placeholder="Hospital, school, office..." value="{{ $activeFilters['search'] }}" />
                            </label>

                            <label class="space-y-2 text-sm font-medium text-slate-700">
                                Category
                                <select class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="category">
                                    <option value="">All categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->slug }}" @selected($activeFilters['category'] === $category->slug)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="space-y-2 text-sm font-medium text-slate-700">
                                Area
                                <select class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="area">
                                    <option value="">All mapped areas</option>
                                    @foreach ($layers['areas'] as $area)
                                        <option value="{{ $area['slug'] }}" @selected($activeFilters['area'] === $area['slug'])>{{ $area['name'] }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="space-y-2 text-sm font-medium text-slate-700">
                                Source year
                                <select class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="source_year">
                                    <option value="">All source years</option>
                                    @foreach ($sourceYears as $year)
                                        <option value="{{ $year }}" @selected($activeFilters['source_year'] === (string) $year)>{{ $year }}</option>
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
                            <article class="atlas-result-card">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $facility->name }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $facility->facility_type }} &middot; {{ $facility->locality }}</p>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold text-white" style="background-color: {{ $facility->category?->color ?? '#0f766e' }}">
                                        {{ $facility->category?->name ?? 'Facility' }}
                                    </span>
                                </div>
                                <div class="atlas-result-card-actions">
                                    <a class="atlas-inline-link" href="{{ route('facilities.show', $facility) }}">Open detail</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="atlas-card">
                    <p class="atlas-eyebrow">Dedicated explorer</p>
                    <h2 class="mt-2 font-display text-3xl font-semibold text-slate-950">Open the map on its own page.</h2>
                    <p class="mt-4 max-w-xl text-base leading-7 text-slate-600">
                        The explorer route removes the stacked workbench layout and gives the atlas the room it needs. Your current
                        filters are forwarded into the map so the spatial review starts from the same shortlist.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a class="atlas-button atlas-button-primary" id="atlas-open-explorer" href="{{ route('atlas.explore', array_filter($activeFilters)) }}">Open filtered explorer</a>
                        @auth
                            @if (auth()->user()->canAccessAdmin())
                                <a class="atlas-button atlas-button-secondary" href="{{ route('admin.dashboard') }}">Open Admin Console</a>
                            @endif
                        @else
                            <a class="atlas-button atlas-button-secondary" href="{{ route('login') }}">Operator login</a>
                        @endauth
                    </div>
                </div>

                <div class="atlas-card">
                    <p class="atlas-eyebrow">Explorer benefits</p>
                    <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Why the split works better</h2>
                    <div class="mt-6 grid gap-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <p class="font-semibold text-slate-900">Workbench first</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Filter and shortlist facilities without squeezing the map into a narrow panel.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <p class="font-semibold text-slate-900">Map second</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Use a dedicated map stage for boundaries, layer toggles, fullscreen, and spatial inspection.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <p class="font-semibold text-slate-900">Cleaner interview story</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">You can explain the product as two public modes: shortlist review and atlas exploration.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('atlas.partials.dataset-panels')
    </section>

    @push('scripts')
        @include('atlas.partials.workbench-script')
    @endpush
</x-app-layout>
