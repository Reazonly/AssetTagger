<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Manajemen Aset') - AssetTagger</title>

    {{-- Scripts & Styles --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Style yang dibutuhkan oleh Livewire --}}
    @livewireStyles

    <style>
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">
    <div x-data="{ isSidebarOpen: true }" class="flex h-screen bg-gray-100">

        {{-- Memanggil Sidebar dari file terpisah --}}
        @include('layouts.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm border-b">
                <div class="flex items-center justify-between px-6 h-20">
                    <div class="flex items-center">
                        <button @click="isSidebarOpen = !isSidebarOpen" class="text-gray-500 focus:outline-none p-2 rounded-md">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <h1 class="text-xl font-semibold text-gray-700 ml-4">Manajemen Aset Jhonlin Group</h1>
                    </div>
                    <div class="flex items-center">
                        <img src="{{ asset('images/jhonlin_logo.png') }}" alt="Jhonlin Group Logo" class="h-10">
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="container mx-auto px-6 py-8">
                    {{-- Baris ini akan menampilkan konten dari Livewire ($slot) dan halaman Blade biasa (@yield) --}}
                    {!! $slot ?? $__env->yieldContent('content') !!}
                </div>
            </main>
        </div>
    </div>
    
    @stack('scripts')

    {{-- Script utama Livewire yang juga memuat Alpine.js --}}
    @livewireScripts
</body>
</html>
