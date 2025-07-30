<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Manajemen Aset') - AssetTagger</title>

    <link rel="preload" href="https://cdn.tailwindcss.com" as="script">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" as="script">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" as="style">

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        /* Base font-family for the body */
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Hide elements that use x-cloak until Alpine.js initializes */
        [x-cloak] {
            display: none !important;
        }

        /* Tailwind CSS custom color configuration */
        /* It's generally better to place this in a tailwind.config.js file
           and compile your CSS for production environments.
           However, for CDN usage, this inline script is acceptable. */
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'jg-green': '#0A9A5D',
                        'jg-green-light': '#44BB7E',
                        'jg-blue': '#263C92',
                        'jg-teal': '#459996',
                    }
                }
            }
        }
    </style>

    {{-- You can add more meta tags or links here if needed --}}
</head>
<body class="bg-gray-100 text-gray-900">
    <div x-data="{ isSidebarOpen: true }" class="flex h-screen bg-gray-100">

        <aside x-show="isSidebarOpen"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-300"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="w-64 bg-jg-blue text-white flex-shrink-0 flex flex-col z-40 md:relative absolute h-full"> {{-- Added z-index and positioning for responsiveness --}}
            {{-- Logo --}}
            <div class="flex items-center justify-center h-20 border-b border-white/20">
                {{-- Ensure the image path is correct relative to your public directory --}}
                <img src="{{ asset('images/jhonlin_logo.png') }}" alt="Jhonlin Group Logo" class="h-10">
            </div>

            {{-- Navigation Menu --}}
            <nav class="mt-6 flex-grow">
                <a href="{{ route('dashboard') }}" class="flex items-center px-6 py-3 transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-white/10' : 'hover:bg-white/10' }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="mx-4 font-medium">Dashboard</span>
                </a>
                <a href="{{ route('assets.index') }}" class="flex items-center px-6 py-3 transition-colors duration-200 {{ request()->routeIs('assets.*') ? 'bg-white/10' : 'hover:bg-white/10' }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="mx-4 font-medium">Manajemen Aset</span>
                </a>
            </nav>

            {{-- User Info & Logout --}}
            @auth
            <div class="px-6 py-4 border-t border-white/20">
                <p class="text-sm font-semibold">{{ Auth::user()->nama_pengguna }}</p>
                <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-4"> {{-- Added margin-top for spacing --}}
                    @csrf
                    <button type="submit" class="flex items-center text-sm font-medium text-red-300 hover:text-red-100 transition-colors duration-200 focus:outline-none"> {{-- Changed text color for better contrast on blue background --}}
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Logout
                    </button>
                </form>
            </div>
            @endauth
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm border-b">
                <div class="flex items-center justify-between px-6 py-4">
                    {{-- Toggle Sidebar Button --}}
                    <button @click="isSidebarOpen = !isSidebarOpen" class="text-gray-500 hover:text-jg-green focus:outline-none p-2 rounded-md"> {{-- Added padding and rounded corners --}}
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    {{-- Logout button in header (optional, if you want it duplicated or only here) --}}
                    {{-- Removed the duplicated logout form from here as it's already in the sidebar @auth block --}}
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="container mx-auto px-6 py-8">
                    @if (session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-5 rounded-md shadow" role="alert">
                            <p class="font-bold">Sukses</p>
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    @if (session('error')) {{-- Added an error message display --}}
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-5 rounded-md shadow" role="alert">
                            <p class="font-bold">Error</p>
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>