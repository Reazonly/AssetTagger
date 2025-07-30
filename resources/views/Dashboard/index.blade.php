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
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-jg-green">
            <p class="text-sm text-gray-500">Total Aset</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalAssets }}</p>
        </div>

        {{-- Total Pengguna --}}
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-jg-teal">
            <p class="text-sm text-gray-500">Total Pengguna</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalUsers }}</p>
        </div>

        {{-- Aset Rusak --}}
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-red-500">
            <p class="text-sm text-gray-500">Aset Rusak</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $assetsRusak }}</p>
        </div>
    </div>
@endsection
