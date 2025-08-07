@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6 md:p-8">

    <div class="border-b border-gray-200 pb-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Manajemen Pengguna</h1>
        <p class="text-sm text-gray-500 mt-1">Ubah hak akses (role) untuk setiap pengguna terdaftar.</p>
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama Pengguna</th>
                    <th scope="col" class="px-6 py-3">Email</th>
                    <th scope="col" class="px-6 py-3">Role Saat Ini</th>
                    <th scope="col" class="px-6 py-3">Ubah Role</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $user->nama_pengguna }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            {{-- PERUBAHAN: Menambahkan style untuk role Editor --}}
                            <span class="px-2 py-1 font-semibold leading-tight rounded-full text-xs
                                @if($user->role == 'admin') bg-red-100 text-red-700
                                @elseif($user->role == 'editor') bg-green-100 text-green-700
                                @elseif($user->role == 'viewer') bg-blue-100 text-blue-700
                                @else bg-gray-200 text-gray-700 @endif">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('users.updateRole', $user->id) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <select name="role" class="block w-full border-gray-300 rounded-md shadow-sm py-1.5 text-sm">
                                    {{-- PERUBAHAN: Menghapus 'User' dan menambahkan 'Editor' --}}
                                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="editor" {{ $user->role == 'editor' ? 'selected' : '' }}>Editor</option>
                                    <option value="viewer" {{ $user->role == 'viewer' ? 'selected' : '' }}>Viewer</option>
                                </select>
                                <button type="submit" class="px-3 py-1.5 bg-sky-600 text-white font-semibold rounded-md hover:bg-sky-700 text-sm">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-gray-500">Tidak ada pengguna lain.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if ($users->hasPages())
        <div class="mt-6">{{ $users->links() }}</div>
    @endif
</div>
@endsection