<x-app-layout>
    @php($title = 'Datasets')

    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="atlas-eyebrow">Source management</p>
                <h1 class="font-display text-4xl font-semibold text-slate-950">Datasets</h1>
            </div>
            <a class="atlas-button atlas-button-secondary" href="{{ route('admin.imports.index') }}">Go to imports</a>
        </div>
    </x-slot>

    <section class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[0.92fr_1.08fr] lg:px-8">
        <div class="atlas-card">
            <p class="atlas-eyebrow">Create dataset</p>
            <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Register a source</h2>

            <form class="mt-6 space-y-4" method="POST" action="{{ route('admin.datasets.store') }}">
                @csrf
                <label class="block space-y-2 text-sm font-medium text-slate-700">
                    Name
                    <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="name" required />
                </label>

                <label class="block space-y-2 text-sm font-medium text-slate-700">
                    Description
                    <textarea class="min-h-28 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="description"></textarea>
                </label>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block space-y-2 text-sm font-medium text-slate-700">
                        Source type
                        <select class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="source_type">
                            <option value="official_kp">Official KP</option>
                            <option value="osm">OSM</option>
                            <option value="manual">Manual</option>
                            <option value="demo">Demo</option>
                        </select>
                    </label>

                    <label class="block space-y-2 text-sm font-medium text-slate-700">
                        Cadence
                        <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="cadence" placeholder="Monthly, ad hoc..." />
                    </label>
                </div>

                <label class="block space-y-2 text-sm font-medium text-slate-700">
                    Source URL
                    <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="source_url" type="url" placeholder="https://..." />
                </label>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block space-y-2 text-sm font-medium text-slate-700">
                        License
                        <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="license" />
                    </label>

                    <label class="block space-y-2 text-sm font-medium text-slate-700">
                        Owner
                        <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm" name="owner_name" />
                    </label>
                </div>

                <button class="atlas-button atlas-button-primary" type="submit">Create dataset</button>
            </form>
        </div>

        <div class="atlas-card">
            <p class="atlas-eyebrow">Catalog</p>
            <h2 class="mt-2 font-display text-2xl font-semibold text-slate-950">Registered sources</h2>

            <div class="mt-6 overflow-hidden rounded-[1.75rem] border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Dataset</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 font-medium">Versions</th>
                            <th class="px-4 py-3 font-medium">Owner</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($datasets as $dataset)
                            <tr>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-900">{{ $dataset->name }}</p>
                                    <p class="text-slate-500">{{ $dataset->license ?? 'license pending' }}</p>
                                </td>
                                <td class="px-4 py-4 text-slate-600">{{ $dataset->source_type }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $dataset->versions_count }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $dataset->owner_name ?? 'n/a' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-app-layout>
