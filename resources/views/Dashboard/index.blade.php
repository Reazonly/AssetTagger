@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Ringkasan data aset perusahaan.</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Total Aset --}}
        <div class="bg-white p-6 rounded-lg shadow border border-gray-200 flex items-center">
            <div class="bg-emerald-100 text-emerald-600 p-3 rounded-lg mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Aset</p>
                <p class="text-3xl font-bold text-gray-800">{{ $totalAssets }}</p>
            </div>
        </div>

        {{-- Total Pengguna --}}
        <div class="bg-white p-6 rounded-lg shadow border border-gray-200 flex items-center">
            <div class="bg-blue-100 text-blue-600 p-3 rounded-lg mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197m0 0A10.99 10.99 0 0012 13a10.99 10.99 0 00-3-1.197z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Pengguna</p>
                <p class="text-3xl font-bold text-gray-800">{{ $totalUsers }}</p>
            </div>
        </div>

        {{-- Aset Rusak --}}
        <div class="bg-white p-6 rounded-lg shadow border border-gray-200 flex items-center">
            <div class="bg-red-100 text-red-600 p-3 rounded-lg mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Aset Rusak</p>
                <p class="text-3xl font-bold text-gray-800">{{ $assetsRusak }}</p>
            </div>
        </div>
    </div>

    {{-- You can add charts or other components here later --}}
@endsection