@extends('layouts.app')
@section('title', 'Sub-Kategori untuk ' . $category->name)
@section('content')
<div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Sub-Kategori: <span class="text-sky-600">{{ $category->name }}</span></h1>
        </div>
       <div class="mt-4 md:mt-0 flex items-center gap-3">
    
    <a href="{{ route('master-data.sub-categories.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors py-2 px-4 rounded-lg bg-gray-200 hover:bg-gray-300 border border-black inline-flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
        Kembali
    </a>

    <a href="{{ route('master-data.sub-categories.create', $category->id) }}" class="inline-flex items-center gap-2 bg-emerald-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-emerald-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        <span>Tambah Sub-Kategori</span>
    </a>

</div>
    </div>
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama Sub-Kategori</th>
                    <th scope="col" class="px-6 py-3">Field Spesifikasi Kustom</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @forelse ($subCategories as $subCategory)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $subCategory->name }}</td>
                        <td class="px-6 py-4">
                            @if(!empty($subCategory->spec_fields))
                                @foreach($subCategory->spec_fields as $field)
                                    <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-xs font-semibold text-gray-700 mr-2 mb-2">{{ $field }}</span>
                                @endforeach
                            @else
                                <span class="text-gray-400 text-xs italic">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-4">
                                <a href="{{ route('master-data.sub-categories.edit', $subCategory->id) }}" class="font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                <form action="{{ route('master-data.sub-categories.destroy', $subCategory->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center py-10 text-gray-500">Belum ada sub-kategori untuk kategori ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($subCategories->hasPages())<div class="mt-6">{{ $subCategories->links() }}</div>@endif
</div>
@endsection