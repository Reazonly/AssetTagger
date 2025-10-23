@extends('layouts.app')
@section('title', 'Manajemen Pengguna')

@section('content')
<div x-data="{
    showEditModal: false, // Hanya satu state modal
    selectedUser: null,
    currentUserRoles: [], // Ganti nama agar lebih jelas
    currentUserCompanies: [], // Ganti nama agar lebih jelas
    editActionUrl: '' // Hanya satu action URL
}" class="bg-white rounded-xl shadow-lg p-6 md:p-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Pengguna</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola pengguna yang dapat mengakses sistem.</p>
        </div>
        @can('manage-roles')
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm mt-4 md:mt-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pengguna
            </a>
        @endcan
    </div>

    {{-- Form Pencarian --}}
    <div class="mb-4">
        <form action="{{ route('users.index') }}" method="GET">
            <div class="relative w-full md:w-1/3">
                <input type="text" name="search" placeholder="Cari nama atau email..." value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Pengguna --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b-2 border-black">
                <tr class="divide-x divide-gray-300 text-center">
                    <th class="px-6 py-3">Nama Pengguna</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">Akses Perusahaan</th>
                    <th class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-b hover:bg-gray-50 divide-x divide-gray-200 text-center">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $user->nama_pengguna }}</td>
                        <td class="px-6 py-4">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @foreach ($user->roles as $role)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 mr-1 mb-1 inline-block">{{ $role->display_name }}</span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4">
                            @if($user->hasRole('super-admin'))
                                <span class="italic text-gray-500">Semua Perusahaan</span>
                            @else
                                @forelse ($user->companies as $company)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 mr-1 mb-1 inline-block">{{ $company->code }}</span>
                                @empty
                                    <span class="italic text-gray-500">Tidak ada</span>
                                @endforelse
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{-- Hanya satu tombol Edit Akses (kecuali untuk Super Admin) --}}
                            @if ($user->id !== 1 && !$user->hasRole('super-admin') && auth()->user()->can('assign-role'))
                                <button
                                    @click="showEditModal = true;
                                            selectedUser = {{ $user->id }};
                                            currentUserRoles = {{ $user->roles->pluck('id') }};
                                            currentUserCompanies = {{ $user->companies->pluck('id') }};
                                            editActionUrl = '{{ route('users.update-access', $user->id) }}'"
                                    class="font-medium text-indigo-600 hover:text-indigo-800 mr-3">
                                    Edit Akses
                                </button>
                            @endif

                            {{-- Tombol Reset & Hapus tetap ada (hanya untuk Super Admin) --}}
                            @can('manage-roles')
                                @if ($user->id !== 1)
                                <form action="{{ route('users.resetPassword', $user->id) }}" method="POST" class="inline ml-3" onsubmit="return confirm('Reset password untuk pengguna ini?')">
                                    @csrf
                                    <button type="submit" class="font-medium text-yellow-600 hover:text-yellow-800">Reset Pass</button>
                                </form>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline ml-3" onsubmit="return confirm('Hapus pengguna ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-500">
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Pengguna tidak ditemukan.</h3>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $users->links() }}</div>


    {{-- Modal Edit Akses (Gabungan Role & Perusahaan) --}}
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300">
        <div @click.away="showEditModal = false"
             x-show="showEditModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
             class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4"> {{-- Max width diperbesar sedikit --}}
            
            <form :action="editActionUrl" method="POST" class="p-6">
                @csrf
                <h3 class="text-lg font-bold text-gray-900 mb-6 border-b pb-3">Edit Akses Pengguna</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[60vh] overflow-y-auto pr-3"> {{-- Layout 2 kolom & scroll --}}
                    
                    {{-- Bagian Role --}}
                    <div>
                        <h4 class="text-md font-semibold text-gray-800 mb-3">Role Pengguna</h4>
                        <div class="space-y-3">
                            <p class="text-xs text-gray-600">Pilih minimal satu role.</p>
                            @foreach ($roles as $role)
                                {{-- Jangan tampilkan role Super Admin sebagai pilihan --}}
                                @if($role->name !== 'super-admin') 
                                <label class="flex items-center p-3 rounded-md border hover:bg-gray-50 transition-colors cursor-pointer">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                           x-model="currentUserRoles" 
                                           class="h-4 w-4 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300">
                                    <span class="ml-3 text-sm font-medium text-gray-800">{{ $role->display_name }}</span>
                                </label>
                                @endif
                            @endforeach
                        </div>
                         @error('roles') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                    </div>

                    {{-- Bagian Perusahaan --}}
                    <div>
                        <h4 class="text-md font-semibold text-gray-800 mb-3">Hak Akses Perusahaan</h4>
                         <div class="space-y-3">
                            <p class="text-xs text-gray-600">Pilih perusahaan yang datanya bisa diakses.</p>
                            {{-- Pastikan $companies dikirim dari controller --}}
                            @foreach ($companies as $company)
                                <label class="flex items-center p-3 rounded-md border hover:bg-gray-50 transition-colors cursor-pointer">
                                    <input type="checkbox" name="companies[]" value="{{ $company->id }}" 
                                           x-model="currentUserCompanies" 
                                           class="h-4 w-4 rounded text-purple-600 focus:ring-purple-500 border-gray-300">
                                    <span class="ml-3 text-sm font-medium text-gray-800">{{ $company->name }} ({{ $company->code }})</span>
                                </label>
                            @endforeach
                        </div>
                         @error('companies') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                         @error('companies.*') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                    </div>

                </div> {{-- End grid --}}

                {{-- Tombol Aksi Modal --}}
                <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

