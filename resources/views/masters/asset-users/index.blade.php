@extends('layouts.app')

@section('title', 'Master Data Pengguna Aset')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Master Pengguna Aset</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola daftar pengguna yang dapat dipilih sebagai pemegang aset.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('master-data.asset-users.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                <span>Tambah Pengguna Aset</span>
            </a>
        </div>
    </div>

    <div class="mb-6">
    <form action="{{ route('master-data.asset-users.index') }}" method="GET">
        <div class="relative w-full md:w-1/3">
            <input 
                type="text" 
                name="search" 
                placeholder="Cari nama, jabatan, departemen..." 
                value="{{ request('search') }}" 
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                onchange="this.form.submit()">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </form>
</div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr class="text-center">
                    <th scope="col" class="px-6 py-3">Nama</th>
                    <th scope="col" class="px-6 py-3">Jabatan</th>
                    <th scope="col" class="px-6 py-3">Departemen</th>
                    <th scope="col" class="px-6 py-3">Perusahaan</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @forelse ($assetUsers as $user)
                    <tr class="hover:bg-gray-50 text-center">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $user->nama }}</td>
                        <td class="px-6 py-4">{{ $user->jabatan ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $user->departemen ?? '-' }}</td>
                        <td class="px-6 py-4">{{ optional($user->company)->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-4">
                                <a href="{{ route('master-data.asset-users.edit', $user->id) }}" class="font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                <form action="{{ route('master-data.asset-users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus pengguna ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-500">Tidak ada data pengguna aset yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if ($assetUsers->hasPages())
        <div class="mt-6">{{ $assetUsers->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
