<script>
    const filterForm = document.getElementById('atlas-filter-form');
    const resultsContainer = document.getElementById('atlas-results');
    const countBadge = document.getElementById('atlas-count');
    const explorerLink = document.getElementById('atlas-open-explorer');
    const baseExplorerUrl = @json(route('atlas.explore'));

    function updateExplorerLink(params) {
        const query = new URLSearchParams(params);
        const queryString = query.toString();
        explorerLink.href = queryString ? `${baseExplorerUrl}?${queryString}` : baseExplorerUrl;
    }

    function syncExplorerLinkFromForm() {
        updateExplorerLink(new FormData(filterForm));
    }

    function renderResults(facilities) {
        resultsContainer.innerHTML = '';

        facilities.slice(0, 10).forEach((facility) => {
            const item = document.createElement('article');
            item.className = 'atlas-result-card';
            item.innerHTML = `
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">${facility.name}</p>
                        <p class="mt-1 text-sm text-slate-500">${facility.facility_type ?? 'Facility'} &middot; ${facility.locality ?? 'Peshawar'}</p>
                    </div>
                    <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">${facility.category ?? 'Facility'}</span>
                </div>
                <div class="atlas-result-card-actions">
                    <a class="atlas-inline-link" href="${facility.detail_url}">Open detail</a>
                </div>
            `;

            resultsContainer.appendChild(item);
        });
    }

    async function loadFacilities() {
        const params = new URLSearchParams(new FormData(filterForm));
        const response = await fetch(`/api/facilities?${params.toString()}`);
        const payload = await response.json();

        countBadge.textContent = `${payload.count} visible`;
        renderResults(payload.data);
        updateExplorerLink(params);
    }

    filterForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        await loadFacilities();
    });

    document.getElementById('atlas-reset').addEventListener('click', async () => {
        filterForm.reset();
        syncExplorerLinkFromForm();
        await loadFacilities();
    });

    filterForm.querySelectorAll('input, select').forEach((field) => {
        field.addEventListener('input', syncExplorerLinkFromForm);
        field.addEventListener('change', syncExplorerLinkFromForm);
    });

    loadFacilities();
</script>
