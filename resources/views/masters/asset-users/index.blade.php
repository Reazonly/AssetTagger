@extends('layouts.app')
@section('title', 'Master Data Pengguna Aset')
@section('content')
<div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
    <div class="flex justify-between items-center border-b pb-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Master Data Pengguna Aset</h1>
        <a href="{{ route('master-data.asset-users.create') }}" class="px-4 py-2 bg-sky-600 text-white font-semibold rounded-lg hover:bg-sky-700">Tambah Pengguna</a>
    </div>
    <div class="overflow-x-auto border rounded-lg">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left">Nama</th>
                    <th class="px-6 py-3 text-left">Jabatan</th>
                    <th class="px-6 py-3 text-left">Departemen</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($assetUsers as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $user->nama }}</td>
                        <td class="px-6 py-4">{{ $user->jabatan ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $user->departemen ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-4">
                                <a href="{{ route('master-data.asset-users.edit', $user->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('master-data.asset-users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-10">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($assetUsers->hasPages())<div class="mt-6">{{ $assetUsers->links() }}</div>@endif
</div>
@endsection