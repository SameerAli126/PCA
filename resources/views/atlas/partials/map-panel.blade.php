<div class="atlas-card atlas-map-shell">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="atlas-eyebrow">Public atlas</p>
            <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Interactive facility map</h2>
        </div>
        <div class="text-sm text-slate-500" id="atlas-map-status">
            Map tools ready. Scroll, drag, click, and inspect.
        </div>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[18rem_minmax(0,1fr)]">
        <aside class="atlas-map-sidebar">
            <div class="atlas-map-sidebar-card">
                <p class="atlas-eyebrow">Operator tools</p>
                <h3 class="mt-2 text-lg font-semibold text-slate-950">Map controls</h3>
                <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                    <button class="atlas-button atlas-button-primary atlas-map-button" id="atlas-fit-results" type="button">Fit results</button>
                    <button class="atlas-button atlas-button-secondary atlas-map-button" id="atlas-focus-area" type="button">Focus area</button>
                    <button class="atlas-button atlas-button-secondary atlas-map-button" id="atlas-toggle-boundary" type="button">Hide boundary</button>
                    <button class="atlas-button atlas-button-secondary atlas-map-button" id="atlas-reset-view" type="button">Reset map</button>
                    <button class="atlas-button atlas-button-secondary atlas-map-button sm:col-span-2 xl:col-span-1" id="atlas-toggle-fullscreen" type="button">Fullscreen</button>
                </div>
                <p class="mt-4 text-sm leading-6 text-slate-600">
                    Wheel zoom is enabled, zoom buttons stay visible, and the map can fly to the active district or filtered results.
                </p>
            </div>

            <div class="atlas-map-sidebar-card">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="atlas-eyebrow">Layer stack</p>
                        <h3 class="mt-2 text-lg font-semibold text-slate-950">Department filters</h3>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-slate-600" id="atlas-layer-count">
                        {{ count($layers['facility_layers']) }} ready
                    </span>
                </div>

                <div class="mt-4 grid gap-2" id="atlas-layer-pills">
                    @foreach ($layers['facility_layers'] as $layer)
                        <button class="atlas-layer-pill" data-layer-category="{{ $layer['slug'] }}" aria-pressed="false" type="button">
                            <span class="atlas-layer-pill__main">
                                <span class="atlas-layer-swatch" style="background-color: {{ $layer['color'] }}"></span>
                                <span>{{ $layer['name'] }}</span>
                            </span>
                            <span class="atlas-layer-pill__count">{{ $layer['count'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="atlas-map-sidebar-card">
                <p class="atlas-eyebrow">Area overlay</p>
                <h3 class="mt-2 text-lg font-semibold text-slate-950" id="atlas-area-name">{{ $defaultArea?->name ?? 'Peshawar District' }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600" id="atlas-area-description">
                    Boundary overlay and service metrics for the selected administrative area.
                </p>
                <div class="mt-4 grid gap-2" id="atlas-area-metrics">
                    <div class="atlas-metric-card">
                        <p class="atlas-metric-label">Status</p>
                        <p class="atlas-metric-value">Loading</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="atlas-map-stage">
            <div class="atlas-map-frame">
                <div id="atlas-map"></div>
                <div class="atlas-map-empty hidden" id="atlas-map-empty">
                    No facilities match the current filter set.
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-500">
                <p id="atlas-map-summary">Showing facilities with live layer styling and district context.</p>
                <p>Tiles from OpenStreetMap. Data provenance stays attached to every facility.</p>
            </div>
        </div>
    </div>
</div>
