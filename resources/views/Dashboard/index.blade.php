@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    {{-- Menampilkan pesan error jika ada masalah saat memuat data --}}
    @isset($dashboardError)
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-5 rounded-md shadow" role="alert">
            <p class="font-bold">Error Dashboard</p>
            <p>{{ $dashboardError }}</p>
        </div>
    @endisset

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Ringkasan data aset perusahaan.</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 flex items-center">
            <div class="bg-blue-100 text-blue-600 p-3 rounded-lg mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Aset</p>
                <p class="text-3xl font-bold text-gray-800">{{ $totalAssets ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 flex items-center">
            <div class="bg-green-100 text-green-600 p-3 rounded-lg mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Aset Baik</p>
                <p class="text-3xl font-bold text-gray-800">{{ $assetsBaik ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 flex items-center">
            <div class="bg-red-100 text-red-600 p-3 rounded-lg mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Aset Rusak</p>
                <p class="text-3xl font-bold text-gray-800">{{ $assetsRusak ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- Asset Flow Chart --}}
    <div class="mt-8 bg-white p-6 rounded-lg shadow-md border border-gray-200">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Statistik Aset Masuk & Keluar (6 Bulan Terakhir)</h2>
        <div class="relative h-96">
            <canvas id="assetFlowChart"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = @json($labels ?? []);
        // Hanya jalankan kode grafik jika ada label (data berhasil dimuat)
        if (labels.length > 0) {
            const ctx = document.getElementById('assetFlowChart').getContext('2d');
            const dataMasuk = @json($dataMasuk ?? []);
            const dataKeluar = @json($dataKeluar ?? []);

            const assetFlowChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Data Masuk',
                            data: dataMasuk,
                            backgroundColor: 'rgba(59, 130, 246, 0.5)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Data Keluar',
                            data: dataKeluar,
                            backgroundColor: 'rgba(239, 68, 68, 0.5)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    });
</script>
@endpush
