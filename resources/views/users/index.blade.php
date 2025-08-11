@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6 md:p-8">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Pengguna</h1>
            <p class="text-sm text-gray-500 mt-1">Ubah hak akses dan kelola pengguna yang terdaftar.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                <span>Tambah Pengguna</span>
            </a>
        </div>
    </div>

    <!-- Search Form -->
    <div class="mb-6">
        <form action="{{ route('users.index') }}" method="GET">
            <div class="flex">
                <input type="text" name="search" placeholder="Cari nama atau email pengguna..." value="{{ request('search') }}" class="w-full md:w-1/3 border-gray-300 rounded-l-md shadow-sm py-2 px-3">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white font-semibold rounded-r-md hover:bg-gray-700">Cari</button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama Pengguna</th>
                    <th scope="col" class="px-6 py-3">Email</th>
                    <th scope="col" class="px-6 py-3">Role</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $user->nama_pengguna }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <form action="{{ route('users.updateRole', $user->id) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <select name="role" class="block w-full border-gray-300 rounded-md shadow-sm py-1.5 text-sm">
                                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="editor" {{ $user->role == 'editor' ? 'selected' : '' }}>Editor</option>
                                    <option value="viewer" {{ $user->role == 'viewer' ? 'selected' : '' }}>Viewer</option>
                                </select>
                                <button type="submit" class="px-3 py-1.5 bg-sky-600 text-white font-semibold rounded-md hover:bg-sky-700 text-sm">Simpan</button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if(auth()->id() === 1)
                                <div class="flex items-center justify-center gap-2">
                                    <form action="{{ route('users.resetPassword', $user->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin mereset password pengguna ini? Password baru akan ditampilkan.');">
                                        @csrf
                                        <button type="submit" class="font-medium text-yellow-600 hover:text-yellow-800 text-sm">Reset Pass</button>
                                    </form>
                                    <span class="text-gray-300">|</span>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Anda yakin ingin menghapus pengguna ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 hover:text-red-800 text-sm">Hapus</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-gray-500">Tidak ada pengguna yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if ($users->hasPages())
        <div class="mt-6">{{ $users->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
