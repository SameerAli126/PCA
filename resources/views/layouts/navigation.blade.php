<nav x-data="{ open: false }" class="border-b border-white/60 bg-white/85 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-8">
            <a href="{{ route('atlas.index') }}" class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-900 text-white shadow-lg shadow-teal-900/20">
                    <span class="text-sm font-bold">GIS</span>
                </div>
                <div>
                    <p class="atlas-eyebrow">Pakistan / KP / Peshawar</p>
                    <p class="font-display text-lg font-semibold text-slate-900">{{ config('civic_atlas.name') }}</p>
                </div>
            </a>

            <div class="hidden items-center gap-2 md:flex">
                <a class="atlas-nav-link {{ request()->routeIs('atlas.index') ? 'atlas-nav-link-active' : '' }}" href="{{ route('atlas.index') }}">Workbench</a>
                <a class="atlas-nav-link {{ request()->routeIs('atlas.explore') ? 'atlas-nav-link-active' : '' }}" href="{{ route('atlas.explore') }}">Explore</a>
                @auth
                    @if (auth()->user()->canAccessAdmin())
                        <a class="atlas-nav-link {{ request()->routeIs('admin.*') ? 'atlas-nav-link-active' : '' }}" href="{{ route('admin.dashboard') }}">Admin</a>
                    @endif
                    <a class="atlas-nav-link {{ request()->routeIs('profile.*') ? 'atlas-nav-link-active' : '' }}" href="{{ route('profile.edit') }}">Profile</a>
                @endauth
            </div>
        </div>

        <div class="hidden items-center gap-3 md:flex">
            @auth
                <div class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-slate-600">
                    {{ implode(' / ', auth()->user()->getRoleNames()->toArray()) ?: 'viewer' }}
                </div>
                <span class="text-sm text-slate-600">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="atlas-button atlas-button-secondary" type="submit">Log out</button>
                </form>
            @else
                <a class="atlas-button atlas-button-secondary" href="{{ route('login') }}">Log in</a>
                <a class="atlas-button atlas-button-primary" href="{{ route('register') }}">Create account</a>
            @endauth
        </div>

        <button @click="open = !open" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 md:hidden">
            Menu
        </button>
    </div>

    <div x-show="open" x-transition class="border-t border-slate-200 bg-white px-4 py-4 md:hidden">
        <div class="space-y-2">
            <a class="atlas-mobile-link" href="{{ route('atlas.index') }}">Workbench</a>
            <a class="atlas-mobile-link" href="{{ route('atlas.explore') }}">Explore</a>
            @auth
                @if (auth()->user()->canAccessAdmin())
                    <a class="atlas-mobile-link" href="{{ route('admin.dashboard') }}">Admin</a>
                @endif
                <a class="atlas-mobile-link" href="{{ route('profile.edit') }}">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="atlas-mobile-link w-full text-left" type="submit">Log out</button>
                </form>
            @else
                <a class="atlas-mobile-link" href="{{ route('login') }}">Log in</a>
                <a class="atlas-mobile-link" href="{{ route('register') }}">Create account</a>
            @endauth
        </div>
    </div>
</nav>
