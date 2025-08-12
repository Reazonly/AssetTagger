@extends('layouts.app')
@section('title', 'Master Data Sub-Kategori')
@section('content')
<div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
    <div class="border-b border-gray-200 pb-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Master Sub-Kategori</h1>
        <p class="text-sm text-gray-500 mt-1">Pilih kategori untuk melihat atau mengelola sub-kategorinya.</p>
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama Kategori</th>
                    <th scope="col" class="px-6 py-3">Jumlah Sub-Kategori</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @forelse ($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $category->name }}</td>
                        <td class="px-6 py-4">{{ $category->sub_categories_count }}</td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('master-data.sub-categories.show', $category->id) }}" class="font-medium text-emerald-600 hover:text-emerald-800">Lihat Data</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center py-10 text-gray-500">Silakan tambah data Kategori terlebih dahulu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($categories->hasPages())<div class="mt-6">{{ $categories->links() }}</div>@endif
</div>
@endsection