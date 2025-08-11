@extends('layouts.app')

@section('title', 'Master Data Perusahaan')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6 md:p-8">

    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Master Perusahaan</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola semua data perusahaan yang terdaftar.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('master-data.companies.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                <span>Tambah Perusahaan</span>
            </a>
        </div>
    </div>

   
    <div class="mb-6">
        <form action="{{ route('master-data.companies.index') }}" method="GET">
            <div class="flex">
                <input type="text" name="search" placeholder="Cari nama atau kode perusahaan..." value="{{ request('search') }}" class="w-full md:w-1/3 border-gray-300 rounded-l-md shadow-sm py-2 px-3">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white font-semibold rounded-r-md hover:bg-gray-700">Cari</button>
            </div>
        </form>
    </div>

   
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama Perusahaan</th>
                    <th scope="col" class="px-6 py-3">Kode</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @forelse ($companies as $company)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $company->name }}</td>
                        <td class="px-6 py-4">{{ $company->code }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-4">
                                <a href="{{ route('master-data.companies.edit', $company->id) }}" class="font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                <form action="{{ route('master-data.companies.destroy', $company->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus perusahaan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-10 text-gray-500">Tidak ada data perusahaan yang cocok.</td>
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
