@extends('layouts.app')

@section('title', 'Manajemen Role & Hak Akses')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6 md:p-8">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Role & Hak Akses</h1>
            <p class="text-sm text-gray-500 mt-1">Buat peran baru dan atur hak akses untuk setiap peran.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('roles.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                <span>Tambah Role Baru</span>
            </a>
        </div>
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b-2 border-black">
                <tr class="text-center divide-x divide-gray-300">
                    <th scope="col" class="px-6 py-3">Nama Role</th>
                    <th scope="col" class="px-6 py-3">Nama Tampilan</th>
                    <th scope="col" class="px-6 py-3">Jumlah Hak Akses</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @forelse ($roles as $role)
                    <tr class="border-b hover:bg-gray-50 divide-x divide-gray-200 text-center">
                        <td class="px-6 py-4 font-mono text-gray-500">{{ $role->name }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $role->display_name }}</td>
                        <td class="px-6 py-4">{{ $role->permissions_count }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-4">
                                <a href="{{ route('roles.edit', $role->id) }}" class="font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                @if(!in_array($role->name, ['super-admin', 'admin', 'editor', 'viewer']))
                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus role ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-gray-500">Belum ada role yang dibuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if ($roles->hasPages())
        <div class="mt-6">{{ $roles->links() }}</div>
    @endif
</div>
@endsection