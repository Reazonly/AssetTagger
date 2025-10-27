@extends('layouts.app')

@section('title', 'Tambah Role Baru')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div class="flex items-center gap-4">
             <a href="{{ route('roles.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
            <div>
                 <h1 class="text-3xl font-bold text-gray-800">Tambah Role Baru</h1>
                 <p class="text-sm text-gray-500 mt-1">Definisikan peran baru dan hak aksesnya.</p>
            </div>
        </div>
        {{-- Tombol kembali bisa dipindah ke sini jika mau --}}
    </div>

    {{-- Form --}}
    <form action="{{ route('roles.store') }}" method="POST">
        @csrf
        <div class="bg-white p-8 rounded-lg shadow-md border space-y-6">
            {{-- Input Nama Role --}}
            <div>
                <label for="display_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Tampilan Role <span class="text-red-600">*</span></label>
                <input type="text" name="display_name" id="display_name" value="{{ old('display_name') }}" required placeholder="e.g., Manajer Keuangan"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 @error('display_name') border-red-500 @enderror">
                @error('display_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Role (Slug) <span class="text-red-600">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g., manajer-keuangan (huruf kecil, tanpa spasi)"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-sky-500 focus:ring-sky-500 @error('name') border-red-500 @enderror">
                 <p class="text-xs text-gray-500 mt-1">Gunakan huruf kecil dan tanda hubung (-) sebagai pengganti spasi. Contoh: `lihat-aset`, `kepala-divisi`.</p>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Pilihan Hak Akses (Permissions) --}}
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900">Hak Akses (Permissions)</h3>
                <p class="text-sm text-gray-500 mb-4">Pilih hak akses yang akan dimiliki oleh role ini.</p>
                <div class="space-y-4">
                    {{-- Loop melalui grup permission yang dikirim dari controller --}}
                    {{-- Ini akan otomatis menampilkan grup 'Reports' jika ada permissionnya --}}
                    @foreach ($permissions as $group => $permissionList)
                        <fieldset class="border p-4 rounded-md bg-gray-50/50">
                            {{-- Tampilkan nama grup --}}
                            <legend class="px-2 font-semibold text-sm capitalize text-gray-700">{{ str_replace('-', ' ', $group) }}</legend>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-3 mt-2">
                                {{-- Loop melalui permission dalam grup --}}
                                @foreach ($permissionList as $permission)
                                    <label class="flex items-center space-x-2 cursor-pointer hover:text-sky-700">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                               class="h-4 w-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500"
                                               {{ (is_array(old('permissions')) && in_array($permission->id, old('permissions'))) ? 'checked' : '' }}>
                                        <span class="text-sm text-gray-700">{{ $permission->display_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
                 @error('permissions') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="mt-8 flex gap-4">
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-sky-600 text-white font-semibold rounded-lg hover:bg-sky-700 transition-all shadow">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Simpan Role
            </button>
            <a href="{{ route('roles.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition-all border border-gray-300">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection

