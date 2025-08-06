@extends('layouts.app')

@section('title', 'Master Data - Companies')

@section('content')
{{-- Kontainer Utama Sesuai Permintaan Anda --}}
<div x-data="{ selectedItems: [] }" class="bg-white rounded-xl shadow-lg p-6 md:p-8">
    
    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Master Perusahaan</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola, cari, dan filter semua data perusahaan.</p>
        </div>
        <div class="flex items-center gap-2 mt-4 sm:mt-0">
            <a href="{{ route('master-data.companies.create') }}" class="bg-green-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-green-700 shadow-md transition-colors inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                Tambah Perusahaan
            </a>
        </div>
    </div>

    {{-- Filter dan Aksi (Latar belakang dan bayangan dihilangkan) --}}
    <div class="mb-6">
        <form action="{{ route('master-data.companies.index') }}" method="GET" class="w-full max-w-sm">
            <div class="relative">
                <input type="text" name="search" placeholder="Cari nama, kode..." value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                <svg class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </form>
    </div>
    
    {{-- Konten Tabel (Latar belakang dan bayangan dihilangkan) --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 w-4"><input type="checkbox" @click="selectedItems = $event.target.checked ? {{ $companies->pluck('id') }} : []" class="rounded"></th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nama Perusahaan</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal Dibuat</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($companies as $company)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4"><input type="checkbox" x-model="selectedItems" value="{{ $company->id }}" class="rounded"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $company->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $company->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $company->created_at->isoFormat('D MMM YYYY') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex gap-4 justify-end">
                                <a href="{{ route('master-data.companies.edit', $company) }}" class="text-blue-600 hover:text-blue-900 font-semibold">Edit</a>
                                <form action="{{ route('master-data.companies.destroy', $company) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data perusahaan yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($companies->hasPages())
        <div class="mt-6">{{ $companies->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection