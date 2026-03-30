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
                            <p class="mt-1 text-sm text-slate-500">{{ $dataset->source_type }} &middot; {{ $dataset->license ?? 'license pending' }}</p>
                        </div>
                        <a class="text-sm font-medium text-teal-700" href="{{ $dataset->source_url }}" target="_blank" rel="noreferrer">Source -></a>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $dataset->description }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
