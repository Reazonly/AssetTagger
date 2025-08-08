<aside x-show="isSidebarOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="w-64 bg-sky-800 text-white flex-shrink-0 flex flex-col z-40 md:relative absolute h-full border-r border-gray-300">
    
    {{-- Logo --}}
    <div class="flex items-center justify-center h-20 bg-white border-b border-gray-200">
        <img src="{{ asset('images/jhonlin_logo.png') }}" alt="Jhonlin Group Logo" class="h-16">
    </div>

    {{-- Menu Navigasi --}}
    <nav class="mt-6 flex-grow px-4">
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 
            {{ request()->routeIs('dashboard') ? 'bg-sky-900' : 'text-sky-100 hover:bg-sky-700' }}">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="mx-4 font-medium">Dashboard</span>
        </a>
        <a href="{{ route('assets.index') }}" class="mt-2 flex items-center px-4 py-3 rounded-lg transition-colors duration-200 
            {{ request()->routeIs('assets.*') ? 'bg-sky-900' : 'text-sky-100 hover:bg-sky-700' }}">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span class="mx-4 font-medium">Manajemen Aset</span>
        </a>

        {{-- AREA ADMIN: HANYA UNTUK ROLE ADMIN --}}
        @if(auth()->user()->role == 'admin')
            <div class="mt-6 pt-4 border-t border-sky-700">
                <p class="px-4 text-xs text-sky-300 uppercase tracking-wider font-semibold">Admin Area</p>
                
                {{-- MENU MANAJEMEN PENGGUNA --}}
                <a href="{{ route('users.index') }}" class="mt-2 flex items-center px-4 py-3 rounded-lg transition-colors duration-200 
                   {{ request()->routeIs('users.*') ? 'bg-sky-900' : 'text-sky-100 hover:bg-sky-700' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.125-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.125-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="mx-4 font-medium">Manajemen Pengguna</span>
                </a>

                {{-- MENU MASTER DATA DROPDOWN --}}
                <div x-data="{ open: {{ request()->routeIs('master-data.*') ? 'true' : 'false' }} }" class="mt-2">
                    <button @click="open = !open" class="w-full flex justify-between items-center px-4 py-3 rounded-lg transition-colors duration-200 text-sky-100 hover:bg-sky-700">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4M4 7l8 5 8-5M12 12l8 5" />
                            </svg>
                            <span class="mx-4 font-medium">Master Data</span>
                        </div>
                        <svg x-show="!open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        <svg x-show="open" class="h-5 w-5 transform rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div x-show="open" x-transition class="mt-2 space-y-2 pl-8">
                        <a href="{{ route('master-data.categories.index') }}" class="block px-4 py-2 text-sm rounded-md {{ request()->routeIs('master-data.categories.*') ? 'text-white font-bold' : 'text-sky-200 hover:text-white' }}">Kategori</a>
                        <a href="{{ route('master-data.companies.index') }}" class="block px-4 py-2 text-sm rounded-md {{ request()->routeIs('master-data.companies.*') ? 'text-white font-bold' : 'text-sky-200 hover:text-white' }}">Perusahaan</a>
                    </div>
                </div>
            </div>
        @endif
    </nav>
    
    {{-- Info Pengguna & Logout --}}
    @auth
    <div class="px-6 py-4 mt-auto border-t border-sky-700">
        <div class="flex items-center">
            <div class="h-10 w-10 rounded-full bg-sky-900 flex items-center justify-center mr-3 border-2 border-sky-500 text-lg font-semibold text-white uppercase">
                <a href="{{ route('profile.edit') }}" class="flex w-full items-center text-sm font-medium text-sky-200 hover:text-white transition-colors duration-200">
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
        </div>
        
        {{-- PERUBAHAN: Menambahkan link Profil di sini --}}
        
                @csrf
                <button type="submit" class="flex w-full items-center text-sm font-medium text-red-400 hover:text-red-200 transition-colors duration-200 focus:outline-none">
                    <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
    @endauth
</aside>