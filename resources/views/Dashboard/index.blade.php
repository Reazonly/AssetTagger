@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    @isset($dashboardError)
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-5 rounded-md shadow" role="alert">
            <p class="font-bold">Error Dashboard</p>
            <p>{{ $dashboardError }}</p>
        </div>
    @endisset

    <div class="flex items-center gap-4 mb-6">
        <div class="flex-shrink-0 p-3 bg-sky-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Ringkasan data aset perusahaan per {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY') }}.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-md border"><p class="text-sm text-gray-500">Total Aset</p><p class="text-3xl font-bold text-gray-800">{{ $totalAssets ?? 0 }}</p></div>
        <div class="bg-white p-6 rounded-lg shadow-md border"><p class="text-sm text-gray-500">Kondisi Baik</p><p class="text-3xl font-bold text-green-600">{{ $assetsBaik ?? 0 }}</p></div>
        <div class="bg-white p-6 rounded-lg shadow-md border"><p class="text-sm text-gray-500">Kondisi Rusak</p><p class="text-3xl font-bold text-red-600">{{ $assetsRusak ?? 0 }}</p></div>
        <div class="bg-white p-6 rounded-lg shadow-md border"><p class="text-sm text-gray-500">Dlm. Perbaikan</p><p class="text-3xl font-bold text-yellow-500">{{ $assetsPerbaikan ?? 0 }}</p></div>
        <div class="bg-white p-6 rounded-lg shadow-md border"><p class="text-sm text-gray-500">Total Nilai Aset</p><p class="text-xl font-bold text-gray-800">Rp {{ number_format($totalNilaiAset ?? 0, 0, ',', '.') }}</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Aset per Kategori</h2>
            <div class="relative h-72">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Aset per Perusahaan</h2>
            <div class="relative h-72">
                <canvas id="companyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 mt-8">
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md border">
             <h2 class="text-xl font-bold text-gray-800 mb-4">5 Aset Terbaru</h2>
            <div class="space-y-4">
                @forelse($recentAssets ?? [] as $asset)
                    <div class="border-b pb-3 last:border-0">
                        <a href="{{ route('assets.show', $asset->id) }}" class="font-semibold text-sky-600 hover:underline">{{ $asset->nama_barang }}</a>
                        <p class="text-xs text-gray-500">{{ $asset->code_asset }}</p>
                        <p class="text-xs text-gray-400">Ditambahkan: {{ $asset->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Tidak ada data aset.</p>
                @endforelse
            </div>
        </div>
        <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-md border">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Statistik Aset Masuk (6 Bulan Terakhir)</h2>
            <div class="relative h-96">
                <canvas id="assetFlowChart"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const generateRandomColors = (numColors) => {
            const colors = [];
            for (let i = 0; i < numColors; i++) {
                const r = Math.floor(Math.random() * 200);
                const g = Math.floor(Math.random() * 200);
                const b = Math.floor(Math.random() * 200);
                colors.push(`rgba(${r}, ${g}, ${b}, 0.6)`);
            }
            return colors;
        };

       
        const categoryData = @json($assetsByCategory ?? []);
        if (categoryData.length > 0) {
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(c => c.name),
                    datasets: [{
                        data: categoryData.map(c => c.assets_count),
                        backgroundColor: generateRandomColors(categoryData.length),
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }


        const companyData = @json($assetsByCompany ?? []);
        if (companyData.length > 0) {
            const companyCtx = document.getElementById('companyChart').getContext('2d');
            new Chart(companyCtx, {
                type: 'pie',
                data: {
                    labels: companyData.map(c => c.name),
                    datasets: [{
                        data: companyData.map(c => c.assets_count),
                        backgroundColor: generateRandomColors(companyData.length),
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

    
        const flowLabels = @json($labels ?? []);
        if (flowLabels.length > 0) {
            const flowCtx = document.getElementById('assetFlowChart').getContext('2d');
            new Chart(flowCtx, {
                type: 'bar',
                data: {
                    labels: flowLabels,
                    datasets: [
                      
                        { label: 'Aset Masuk', data: @json($dataMasuk ?? []), backgroundColor: 'rgba(59, 130, 246, 0.5)' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
            });
        }
    });
</script>
@endpush
