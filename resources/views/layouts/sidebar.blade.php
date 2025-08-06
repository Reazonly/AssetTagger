<aside x-show="isSidebarOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="w-64 bg-sky-600 text-white flex-shrink-0 flex flex-col z-40 md:relative absolute h-full border-r border-gray-300">
    
    <div class="flex items-center justify-center h-20 bg-white border-b border-gray-200">
        <img src="{{ asset('images/jhonlin_logo.png') }}" alt="Jhonlin Group Logo" class="h-20">
    </div>

    <nav class="mt-6 flex-grow px-4">
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-sky-700' : 'text-white hover:bg-sky-700' }}">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="mx-4 font-medium">Dashboard</span>
        </a>
        <a href="{{ route('assets.index') }}" class="mt-2 flex items-center px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('assets.*') ? 'bg-sky-700' : 'text-white hover:bg-sky-700' }}">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span class="mx-4 font-medium">Manajemen Aset</span>
        </a>
        <div x-data="{ open: {{ request()->routeIs('master-data.*') ? 'true' : 'false' }} }" class="mt-2">
            <button @click="open = !open" class="w-full flex justify-between items-center px-4 py-3 rounded-lg transition-colors duration-200 text-white hover:bg-sky-700">
                <span class="flex items-center">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10m16-5H4m16 5H4M4 7l4-4 4 4M4 17l4 4 4-4" /></svg>
                    <span class="mx-4 font-medium">Master Data</span>
                </span>
                <svg class="h-5 w-5 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>
            <div x-show="open" x-transition class="mt-2 space-y-1 pl-8">
                <a href="{{ route('master-data.companies.index') }}" class="block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('master-data.companies.*') ? 'bg-sky-700' : 'text-white hover:bg-sky-700' }}">Companies</a>
                <a href="{{ route('master-data.categories.index') }}" class="block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('master-data.categories.*') ? 'bg-sky-700' : 'text-white hover:bg-sky-700' }}">Categories</a>
            </div>
        </div>
    </nav>

    @auth
    <div class="px-6 py-4 border-t border-white/20">
        <div class="flex items-center">
            <div class="h-10 w-10 rounded-full bg-sky-700 flex items-center justify-center mr-3 border border-white text-lg font-semibold text-white uppercase">
                @php
                    $nameParts = explode(' ', Auth::user()->nama_pengguna);
                    $initials = count($nameParts) > 1 ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1)) : strtoupper(substr($nameParts[0], 0, 1));
                @endphp
                {{ $initials }}
            </div>
            <div>
                <p class="text-sm font-semibold">{{ Auth::user()->nama_pengguna }}</p>
                <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="flex items-center text-sm font-medium text-red-300 hover:text-red-100 transition-colors duration-200 focus:outline-none">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                Logout
            </button>
        </form>
    </div>
    @endauth
</aside>