<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-g">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Manajemen Aset')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">
    <div id="app" class="min-h-screen flex flex-col">
        {{-- Header dengan Aksen Hijau --}}
        <header class="bg-white shadow-md sticky top-0 z-10">
            <nav class="container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <img src="{{ asset('images/jhonlin_logo.png') }}" alt="Logo Jhonlin Group" class="h-16">
                    <a href="{{ route('assets.index') }}" class="text-2xl font-bold text-gray-800 hover:text-emerald-600 transition-colors">
                        AssetTagger
                    </a>
                

                    
                    {{-- Menampilkan tombol logout hanya jika pengguna sudah login --}}
                    @auth
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">Halo, {{ Auth::user()->nama_pengguna }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="bg-red-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-red-600 transition-colors text-sm">
                                Logout
                            </button>
                        </form>
                    </div>
                    @endauth
                </div>
            </nav>
        </header>

        <main class="flex-grow container mx-auto p-4 sm:p-6">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-5 rounded-md shadow" role="alert">
                    <p class="font-bold">Sukses</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-5 rounded-md shadow" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                @yield('content')
            </div>
        </main>

        <footer class="bg-white mt-auto">
            <div class="container mx-auto px-6 py-4 text-center text-gray-500">
                &copy; {{ date('Y') }} Aplikasi Manajemen Aset.
            </div>
        </footer>
    </div>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
