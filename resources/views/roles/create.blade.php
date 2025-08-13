@extends('layouts.app')

@section('title', 'Tambah Role Baru')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div class="flex items-center gap-4">
            <div class="flex-shrink-0 p-3 bg-sky-100 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Tambah Role Baru</h1>
                <p class="text-sm text-gray-500 mt-1">Definisikan peran baru dan tetapkan hak aksesnya.</p>
            </div>
        </div>
        <div class="mt-4 md:mt-0">
                     <a href="{{ route('roles.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors py-2 px-4 rounded-lg bg-gray-200 hover:bg-gray-300 border border-black inline-flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
            Kembali
        </a>
        </div>
    </div>
    
    <form action="{{ route('roles.store') }}" method="POST">
        @csrf
        <div class="bg-white p-8 rounded-lg shadow-md border space-y-6">
            <div>
                <label for="display_name" class="block text-sm font-medium text-gray-700">Nama Tampilan Role</label>
                <input type="text" name="display_name" id="display_name" value="{{ old('display_name') }}" required placeholder="e.g., Kepala Gudang"
                       class="block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 shadow-sm py-2 px-3 @error('display_name') border-red-500 @enderror">
                @error('display_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Role (Slug)</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g., kepala-gudang (tanpa spasi, huruf kecil)"
                       class="block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 shadow-sm py-2 px-3 @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900">Hak Akses (Permissions)</h3>
                <p class="text-sm text-gray-500 mb-4">Pilih hak akses yang akan dimiliki oleh role ini.</p>
                <div class="space-y-4">
                    @foreach ($permissions as $group => $permissionList)
                        <fieldset class="border p-4 rounded-md">
                            <legend class="px-2 font-semibold text-sm capitalize">{{ str_replace('-', ' ', $group) }}</legend>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mt-2">
                                @foreach ($permissionList as $permission)
                                    <label class="flex items-center space-x-3">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                               class="h-4 w-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500">
                                        <span class="text-sm text-gray-700">{{ $permission->display_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-8 flex gap-4">
            <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700">
                Simpan Role
            </button>
            <a href="{{ route('roles.index') }}" class="text-gray-600 font-semibold px-4 py-2 rounded-lg hover:bg-gray-200">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
