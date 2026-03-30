<script>
    const atlasLayers = {{ Illuminate\Support\Js::from($layers) }};
    const filterForm = document.getElementById('atlas-filter-form');
    const resultsContainer = document.getElementById('atlas-results');
    const countBadge = document.getElementById('atlas-count');
    const areaSelect = filterForm.elements.namedItem('area');
    const categorySelect = filterForm.elements.namedItem('category');
    const searchInput = filterForm.elements.namedItem('search');
    const sourceYearSelect = filterForm.elements.namedItem('source_year');
    const mapStatus = document.getElementById('atlas-map-status');
    const mapSummary = document.getElementById('atlas-map-summary');
    const emptyState = document.getElementById('atlas-map-empty');
    const layerCount = document.getElementById('atlas-layer-count');
    const areaName = document.getElementById('atlas-area-name');
    const areaDescription = document.getElementById('atlas-area-description');
    const areaMetrics = document.getElementById('atlas-area-metrics');
    const fullscreenButton = document.getElementById('atlas-toggle-fullscreen');
    const boundaryButton = document.getElementById('atlas-toggle-boundary');
    const mapStage = document.querySelector('.atlas-map-stage');
    const defaultAreaSlug = areaSelect.value || atlasLayers.areas?.[0]?.slug || null;
    const categoryMeta = new Map((atlasLayers.facility_layers ?? []).map((layer) => [layer.slug, layer]));
    const markerLayer = L.featureGroup();
    const areaLayer = L.geoJSON([], {
        style: {
            color: '#0f766e',
            weight: 2,
            opacity: 0.9,
            fillColor: '#14b8a6',
            fillOpacity: 0.08,
            dashArray: '10 6',
        },
    });
    const markersById = new Map();
    const resultCardsById = new Map();
    let boundaryVisible = true;
    let activeFacilityId = null;
    let hoveredFacilityId = null;

    const map = L.map('atlas-map', {
        scrollWheelZoom: true,
        zoomControl: false,
        boxZoom: true,
        doubleClickZoom: true,
        preferCanvas: true,
    }).setView([atlasLayers.base_map.center.lat, atlasLayers.base_map.center.lng], atlasLayers.base_map.zoom);

    L.tileLayer(atlasLayers.base_map.tile_url, {
        attribution: atlasLayers.base_map.tile_attribution,
        maxZoom: 18,
    }).addTo(map);

    L.control.zoom({ position: 'bottomright' }).addTo(map);
    L.control.scale({ metric: true, imperial: false, position: 'bottomleft' }).addTo(map);

    markerLayer.addTo(map);
    areaLayer.addTo(map);

    function setMapStatus(message) {
        mapStatus.textContent = message;
    }

    function setMapSummary(message) {
        mapSummary.textContent = message;
    }

    function updateBoundaryButton() {
        boundaryButton.textContent = boundaryVisible ? 'Hide boundary' : 'Show boundary';
    }

    function syncLayerButtons() {
        const activeCategory = categorySelect.value;

        document.querySelectorAll('[data-layer-category]').forEach((button) => {
            const isActive = activeCategory !== '' && button.dataset.layerCategory === activeCategory;
            button.classList.toggle('atlas-layer-pill-active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        layerCount.textContent = activeCategory ? '1 filtered' : `${atlasLayers.facility_layers.length} ready`;
    }

    function applyMarkerState(marker, state = 'default') {
        const color = marker.featureProperties?.color ?? '#0f766e';
        const stateStyles = {
            default: { radius: 7, weight: 1.5, fillOpacity: 0.82 },
            hover: { radius: 9.5, weight: 2.5, fillOpacity: 0.94 },
            active: { radius: 11, weight: 3, fillOpacity: 1 },
        };

        marker.setStyle({
            color,
            fillColor: color,
            ...stateStyles[state],
        });

        if (state !== 'default') {
            marker.bringToFront();
        }
    }

    function refreshMarkerStates() {
        markersById.forEach((marker, facilityId) => {
            let state = 'default';

            if (facilityId === hoveredFacilityId) {
                state = 'hover';
            }

            if (facilityId === activeFacilityId) {
                state = 'active';
            }

            applyMarkerState(marker, state);
        });
    }

    function refreshResultStates() {
        resultCardsById.forEach((card, facilityId) => {
            card.classList.toggle('atlas-result-card-active', facilityId === activeFacilityId);
        });
    }

    function setHoveredFacility(facilityId) {
        hoveredFacilityId = facilityId ? String(facilityId) : null;
        refreshMarkerStates();
    }

    function setActiveFacility(facilityId, options = {}) {
        const nextFacilityId = facilityId ? String(facilityId) : null;
        const marker = nextFacilityId ? markersById.get(nextFacilityId) : null;
        const card = nextFacilityId ? resultCardsById.get(nextFacilityId) : null;

        activeFacilityId = nextFacilityId;
        refreshMarkerStates();
        refreshResultStates();

        if (!marker) {
            return;
        }

        if (options.fly !== false) {
            map.flyTo(marker.getLatLng(), Math.max(map.getZoom(), 14), { duration: 0.65 });
        }

        if (options.openPopup !== false) {
            marker.openPopup();
        }

        if (card && options.scroll !== false) {
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function renderAreaMetrics(metrics = []) {
        areaMetrics.innerHTML = '';

        if (!metrics.length) {
            areaMetrics.innerHTML = `
                <div class="atlas-metric-card">
                    <p class="atlas-metric-label">Status</p>
                    <p class="atlas-metric-value">No metrics yet</p>
                </div>
            `;
            return;
        }

        metrics.slice(0, 4).forEach((metric) => {
            const metricCard = document.createElement('div');
            metricCard.className = 'atlas-metric-card';
            metricCard.innerHTML = `
                <p class="atlas-metric-label">${metric.metric_label}</p>
                <p class="atlas-metric-value">
                    ${metric.metric_value}
                    ${metric.unit ? `<span class="text-sm font-medium text-slate-500">${metric.unit}</span>` : ''}
                </p>
            `;
            areaMetrics.appendChild(metricCard);
        });
    }

    async function loadAreaBoundary(areaSlug, options = {}) {
        if (!areaSlug) {
            areaLayer.clearLayers();
            areaName.textContent = 'Peshawar District';
            areaDescription.textContent = 'Boundary overlay and service metrics for the selected administrative area.';
            renderAreaMetrics();
            return;
        }

        const response = await fetch(`/api/areas/${encodeURIComponent(areaSlug)}`);
        const payload = await response.json();

        areaLayer.clearLayers();

        if (payload.area?.feature?.geometry) {
            areaLayer.addData(payload.area.feature);
        }

        areaName.textContent = payload.area.name;
        areaDescription.textContent = `${payload.area.level.replace(/-/g, ' ')} overlay with context metrics for the active selection.`;
        renderAreaMetrics(payload.area.metrics ?? []);

        if (boundaryVisible && !map.hasLayer(areaLayer)) {
            areaLayer.addTo(map);
        }

        if (options.fit) {
            focusArea();
        }
    }

    function focusArea() {
        const areaBounds = areaLayer.getBounds();

        if (areaBounds.isValid()) {
            map.flyToBounds(areaBounds.pad(0.16), {
                duration: 0.7,
                maxZoom: Math.max(atlasLayers.base_map.zoom + 1, 12),
            });
            return;
        }

        map.flyTo([atlasLayers.base_map.center.lat, atlasLayers.base_map.center.lng], atlasLayers.base_map.zoom, {
            duration: 0.7,
        });
    }

    function fitResults() {
        const markerBounds = markerLayer.getBounds();

        if (markerBounds.isValid()) {
            map.flyToBounds(markerBounds.pad(0.18), {
                duration: 0.7,
                maxZoom: 14,
            });
            return;
        }

        focusArea();
    }

    function renderResults(facilities) {
        resultsContainer.innerHTML = '';
        resultCardsById.clear();

        facilities.slice(0, 10).forEach((facility) => {
            const category = categoryMeta.get(facility.category_slug);
            const card = document.createElement('article');
            card.className = 'atlas-result-card';
            card.dataset.facilityId = String(facility.id);
            card.innerHTML = `
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">${facility.name}</p>
                        <p class="mt-1 text-sm text-slate-500">${facility.facility_type ?? 'Facility'} &middot; ${facility.locality ?? 'Peshawar'}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold text-white" style="background-color: ${category?.color ?? '#0f766e'}">
                        ${facility.category ?? 'Facility'}
                    </span>
                </div>
                <div class="atlas-result-card-actions">
                    <button class="atlas-inline-button" data-action="focus" type="button">Focus on map</button>
                    <a class="atlas-inline-link" href="${facility.detail_url}">Open detail</a>
                </div>
            `;

            card.addEventListener('mouseenter', () => setHoveredFacility(facility.id));
            card.addEventListener('mouseleave', () => setHoveredFacility(null));
            card.addEventListener('click', (event) => {
                if (event.target.closest('a')) {
                    return;
                }

                setActiveFacility(facility.id);
            });

            card.querySelector('[data-action="focus"]').addEventListener('click', (event) => {
                event.stopPropagation();
                setActiveFacility(facility.id);
            });

            resultsContainer.appendChild(card);
            resultCardsById.set(String(facility.id), card);
        });

        refreshResultStates();
    }

    async function loadFacilities(options = {}) {
        const params = new URLSearchParams(new FormData(filterForm));
        setMapStatus(options.status ?? 'Refreshing map layers...');

        const response = await fetch(`/api/facilities?${params.toString()}`);
        const payload = await response.json();

        markerLayer.clearLayers();
        markersById.clear();
        resultsContainer.innerHTML = '';
        countBadge.textContent = `${payload.count} visible`;
        emptyState.classList.toggle('hidden', payload.count !== 0);

        payload.geojson.features.forEach((feature) => {
            const facilityId = String(feature.properties.id);
            const marker = L.circleMarker(
                [feature.geometry.coordinates[1], feature.geometry.coordinates[0]],
                {
                    radius: 7,
                    color: feature.properties.color ?? '#0f766e',
                    fillColor: feature.properties.color ?? '#0f766e',
                    fillOpacity: 0.82,
                    weight: 1.5,
                }
            );

            marker.featureProperties = feature.properties;
            marker.bindPopup(`
                <div style="min-width:14rem;">
                    <strong style="display:block;color:#0f172a;">${feature.properties.name}</strong>
                    <div style="margin-top:0.35rem;color:#475569;">${feature.properties.category ?? 'Facility'}</div>
                    <div style="margin-top:0.2rem;color:#64748b;">${feature.properties.facility_type ?? ''}</div>
                    <a href="${feature.properties.detail_url}" style="display:inline-flex;margin-top:0.65rem;color:#0f766e;font-weight:700;">Open detail</a>
                </div>
            `);

            marker.on('mouseover', () => setHoveredFacility(facilityId));
            marker.on('mouseout', () => setHoveredFacility(null));
            marker.on('click', () => setActiveFacility(facilityId, { fly: false, openPopup: true }));

            markerLayer.addLayer(marker);
            markersById.set(facilityId, marker);
        });

        renderResults(payload.data);

        if (payload.count > 0) {
            setMapSummary(`Showing ${Math.min(payload.data.length, 10)} of ${payload.count} facilities. Click a result card or marker to inspect it.`);

            const nextFacilityId = payload.data.some((facility) => String(facility.id) === activeFacilityId)
                ? activeFacilityId
                : String(payload.data[0].id);

            setActiveFacility(nextFacilityId, {
                fly: false,
                openPopup: false,
                scroll: false,
            });

            if (options.fit !== false) {
                fitResults();
            }
        } else {
            activeFacilityId = null;
            hoveredFacilityId = null;
            refreshMarkerStates();
            setMapSummary('No facilities matched the current filter set. Adjust the search, year, or area filters.');
            focusArea();
        }

        setMapStatus(`${payload.count} facilities loaded for the current view.`);
    }

    filterForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        await loadAreaBoundary(areaSelect.value || defaultAreaSlug);
        await loadFacilities({ fit: true, status: 'Applying filter changes...' });
    });

    document.getElementById('atlas-reset').addEventListener('click', async () => {
        filterForm.reset();
        syncLayerButtons();
        await loadAreaBoundary(areaSelect.value || defaultAreaSlug);
        await loadFacilities({ fit: true, status: 'Filters reset. Loading baseline view...' });
    });

    areaSelect.addEventListener('change', async () => {
        await loadAreaBoundary(areaSelect.value || defaultAreaSlug);
    });

    document.querySelectorAll('[data-layer-category]').forEach((button) => {
        button.addEventListener('click', async () => {
            const nextCategory = categorySelect.value === button.dataset.layerCategory ? '' : button.dataset.layerCategory;
            categorySelect.value = nextCategory;
            syncLayerButtons();

            await loadFacilities({
                fit: true,
                status: nextCategory
                    ? `Filtering to ${button.textContent.trim()}.`
                    : 'Showing all facility layers.',
            });
        });
    });

    document.getElementById('atlas-fit-results').addEventListener('click', () => {
        fitResults();
        setMapStatus('Map focused on the current result set.');
    });

    document.getElementById('atlas-focus-area').addEventListener('click', () => {
        focusArea();
        setMapStatus('Map focused on the selected administrative area.');
    });

    boundaryButton.addEventListener('click', () => {
        boundaryVisible = !boundaryVisible;

        if (boundaryVisible) {
            areaLayer.addTo(map);
        } else {
            map.removeLayer(areaLayer);
        }

        updateBoundaryButton();
        setMapStatus(boundaryVisible ? 'Administrative boundary overlay enabled.' : 'Administrative boundary overlay hidden.');
    });

    document.getElementById('atlas-reset-view').addEventListener('click', async () => {
        searchInput.value = '';
        categorySelect.value = '';
        areaSelect.value = defaultAreaSlug ?? '';
        sourceYearSelect.value = '';
        boundaryVisible = true;
        updateBoundaryButton();
        syncLayerButtons();
        await loadAreaBoundary(areaSelect.value || defaultAreaSlug, { fit: true });
        await loadFacilities({ fit: true, status: 'Map reset to the default Peshawar view.' });
    });

    fullscreenButton.addEventListener('click', async () => {
        if (document.fullscreenElement === mapStage) {
            await document.exitFullscreen();
        } else {
            await mapStage.requestFullscreen();
        }
    });

    document.addEventListener('fullscreenchange', () => {
        fullscreenButton.textContent = document.fullscreenElement === mapStage ? 'Exit fullscreen' : 'Fullscreen';
        window.setTimeout(() => map.invalidateSize(), 180);
    });

    syncLayerButtons();
    updateBoundaryButton();

    Promise.resolve()
        .then(() => loadAreaBoundary(areaSelect.value || defaultAreaSlug))
        .then(() => loadFacilities({ fit: true, status: 'Loading public atlas...' }));
</script>
