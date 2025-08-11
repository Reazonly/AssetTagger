<aside x-show="isSidebarOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="w-64 bg-gradient-to-b from-sky-800 to-sky-900 text-white flex-shrink-0 flex flex-col z-40 md:relative absolute h-full border-r border-gray-300">
    
    <div class="flex items-center justify-center h-20 bg-white border-b border-gray-200 shadow-lg">
        <img src="{{ asset('images/jhonlin_logo.png') }}" alt="Jhonlin Group Logo" class="h-16">
    </div>

    
    <nav class="mt-6 flex-grow px-4" 
         x-data="{ 
            activeMenu: '{{ 
                request()->routeIs('dashboard') ? 'dashboard' : 
                (request()->routeIs('assets.*') ? 'assets' : 
                (request()->routeIs('users.*') ? 'users' : 
                (request()->routeIs('master-data.*') ? 'master-data' : ''))) 
            }}',
            isMasterDataOpen: {{ request()->routeIs('master-data.*') ? 'true' : 'false' }}
         }">

        <a href="{{ route('dashboard') }}" 
           @click="activeMenu = 'dashboard'"
           class="flex items-center px-4 py-3 rounded-lg duration-200 transform hover:translate-x-1 transition-all"
           :class="activeMenu === 'dashboard' ? 'bg-sky-900 border-l-4 border-sky-400' : 'text-sky-100 hover:bg-sky-700 border-l-4 border-transparent'">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="mx-4 font-medium">Dashboard</span>
        </a>
        <a href="{{ route('assets.index') }}" 
           @click="activeMenu = 'assets'"
           class="mt-2 flex items-center px-4 py-3 rounded-lg duration-200 transform hover:translate-x-1 transition-all"
           :class="activeMenu === 'assets' ? 'bg-sky-900 border-l-4 border-sky-400' : 'text-sky-100 hover:bg-sky-700 border-l-4 border-transparent'">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span class="mx-4 font-medium">Manajemen Aset</span>
        </a>

        @if(auth()->user()->role == 'admin')
            <div class="mt-6 pt-4 border-t border-sky-700">
                <p class="px-4 text-xs text-sky-300 uppercase tracking-wider font-semibold">Admin Area</p>
                
                <a href="{{ route('users.index') }}" 
                   @click="activeMenu = 'users'"
                   class="mt-2 flex items-center px-4 py-3 rounded-lg duration-200 transform hover:translate-x-1 transition-all"
                   :class="activeMenu === 'users' ? 'bg-sky-900 border-l-4 border-sky-400' : 'text-sky-100 hover:bg-sky-700 border-l-4 border-transparent'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.125-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.125-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span class="mx-4 font-medium">Manajemen User Login</span>
                </a>

                <div class="mt-2">
                    <button @click="isMasterDataOpen = !isMasterDataOpen; activeMenu = 'master-data'"
                            class="w-full flex justify-between items-center px-4 py-3 rounded-lg duration-200 transform hover:translate-x-1 transition-all"
                            :class="activeMenu === 'master-data' ? 'bg-sky-900 border-l-4 border-sky-400' : 'text-sky-100 hover:bg-sky-700 border-l-4 border-transparent'">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4M4 7l8 5 8-5M12 12l8 5" /></svg>
                            <span class="mx-4 font-medium">Master Data</span>
                        </div>
                        <svg class="h-5 w-5 transition-transform duration-200" :class="{'transform rotate-90': isMasterDataOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>

                    <div x-show="isMasterDataOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         class="mt-2 space-y-2 pl-8" x-cloak>
                        <a href="{{ route('master-data.asset-users.index') }}" class="block px-4 py-2 text-sm rounded-md {{ request()->routeIs('master-data.asset-users.*') ? 'text-white font-bold' : 'text-sky-200 hover:text-white' }}">Pengguna Aset</a>
                        <a href="{{ route('master-data.categories.index') }}" class="block px-4 py-2 text-sm rounded-md {{ request()->routeIs('master-data.categories.*') ? 'text-white font-bold' : 'text-sky-200 hover:text-white' }}">Kategori</a>
                        <a href="{{ route('master-data.companies.index') }}" class="block px-4 py-2 text-sm rounded-md {{ request()->routeIs('master-data.companies.*') ? 'text-white font-bold' : 'text-sky-200 hover:text-white' }}">Perusahaan</a>
                    </div>
                </div>
            </div>
        @endif
    </nav>
    
    @auth
    <div class="px-6 py-4 mt-auto border-t border-sky-700">
        <a href="{{ route('profile.edit') }}" class="flex items-center p-2 rounded-lg hover:bg-sky-700 transition-colors duration-200">
            <div class="h-10 w-10 rounded-full bg-sky-900 flex-shrink-0 flex items-center justify-center mr-3 border-2 border-sky-500 text-lg font-semibold text-white uppercase">
                @php
                    $nameParts = explode(' ', Auth::user()->nama_pengguna);
                    $initials = count($nameParts) > 1 ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1)) : strtoupper(substr($nameParts[0], 0, 1));
                @endphp
                {{ $initials }}
            </div>
            <div>
                <p class="text-sm font-semibold text-white">{{ Auth::user()->nama_pengguna }}</p>
                <p class="text-xs text-sky-200 font-medium bg-sky-700 px-2 py-0.5 rounded-full inline-block mt-1">
                    {{ ucfirst(Auth::user()->role) }}
                </p>
            </div>
        </a>
        
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="flex w-full items-center text-sm font-medium text-red-400 hover:text-red-200 transition-colors duration-200 focus:outline-none px-2 py-1">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                Logout
            </button>
        </form>
    </div>
    @endauth
</aside>