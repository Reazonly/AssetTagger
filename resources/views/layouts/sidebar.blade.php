<aside x-show="isSidebarOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="w-64 bg-sky-600 text-white flex-shrink-0 flex flex-col z-40 md:relative absolute h-full border-r border-gray-300">
    
    {{-- Logo --}}
    <div class="flex items-center justify-center h-20 bg-white border-b border-gray-200">
        <img src="{{ asset('images/jhonlin_logo.png') }}" alt="Jhonlin Group Logo" class="h-20">
    </div>

    {{-- Navigation Menu --}}
    <nav class="mt-6 flex-grow px-4">
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 
            {{ request()->routeIs('dashboard') ? 'bg-sky-700' : 'text-white hover:bg-sky-700' }}">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="mx-4 font-medium">Dashboard</span>
        </a>
        <a href="{{ route('assets.index') }}" class="mt-2 flex items-center px-4 py-3 rounded-lg transition-colors duration-200 
            {{ request()->routeIs('assets.*') ? 'bg-sky-700' : 'text-white hover:bg-sky-700' }}">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span class="mx-4 font-medium">Manajemen Aset</span>
        </a>

        {{-- =============================================== --}}
        {{-- BAGIAN BARU: MENU MASTER DATA (HANYA UNTUK ADMIN) --}}
        {{-- =============================================== --}}
        @if(auth()->user()->role == 'admin')
            <div class="mt-6 pt-4 border-t border-sky-700">
                <p class="px-4 text-xs text-sky-200 uppercase tracking-wider font-semibold">Master Data</p>
                {{-- NOTE: Saat ini link masih kosong (#). Anda perlu membuat Controller dan Route untuk halaman-halaman ini. --}}
                <a href="#" class="mt-2 flex items-center px-4 py-3 rounded-lg transition-colors duration-200 text-white hover:bg-sky-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                    <span class="mx-4 font-medium">Kategori</span>
                </a>
                <a href="#" class="mt-2 flex items-center px-4 py-3 rounded-lg transition-colors duration-200 text-white hover:bg-sky-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
                    <span class="mx-4 font-medium">Sub-Kategori</span>
                </a>
                <a href="#" class="mt-2 flex items-center px-4 py-3 rounded-lg transition-colors duration-200 text-white hover:bg-sky-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 12h6m-6 5.25h6M5.25 6h.008v.008H5.25V6zm.75 0h.008v.008H6V6zm.75 0h.008v.008H6.75V6zm.75 0h.008v.008H7.5V6zm.75 0h.008v.008H8.25V6zm.75 0h.008v.008H9V6zm.75 0h.008v.008H9.75V6zm.75 0h.008v.008H10.5V6zm.75 0h.008v.008H11.25V6zm.75 0h.008v.008H12V6zm.75 0h.008v.008H12.75V6zm.75 0h.008v.008H13.5V6zm.75 0h.008v.008H14.25V6zm.75 0h.008v.008H15V6zm.75 0h.008v.008H15.75V6zm.75 0h.008v.008H16.5V6zm.75 0h.008v.008H17.25V6zm.75 0h.008v.008H18V6zm.75 0h.008v.008H18.75V6z" /></svg>
                    <span class="mx-4 font-medium">Perusahaan</span>
                </a>
            </div>
        @endif
    </nav>

    {{-- User Info & Logout --}}
    @auth
    <div class="px-6 py-4 border-t border-white/20">
        <div class="flex items-center mb-2">
            {{-- Profile Picture (Initials) --}}
            <div class="h-10 w-10 rounded-full bg-sky-700 flex items-center justify-center mr-3 border border-white text-lg font-semibold text-white uppercase">
                @php
                    $nameParts = explode(' ', Auth::user()->nama_pengguna);
                    $initials = count($nameParts) > 1 ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1)) : strtoupper(substr($nameParts[0], 0, 1));
                @endphp
                {{ $initials }}
            </div>
            <div>
                <p class="text-sm font-semibold">{{ Auth::user()->nama_pengguna }}</p>
                <p class="text-xs text-sky-200 font-medium bg-sky-700 px-2 py-0.5 rounded-full inline-block mt-1">
                    {{ ucfirst(Auth::user()->role) }}
                </p>
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