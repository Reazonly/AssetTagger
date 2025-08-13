@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Role: {{ $role->display_name }}</h1>
        <a href="{{ route('roles.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">
            &larr; Kembali ke Daftar Role
        </a>
    </div>
    
    <form action="{{ route('roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="bg-white p-8 rounded-lg shadow-md border space-y-6">
            <div>
                <label for="display_name" class="block text-sm font-medium text-gray-700">Nama Tampilan Role</label>
                <input type="text" name="display_name" id="display_name" value="{{ old('display_name', $role->display_name) }}" required placeholder="e.g., Kepala Gudang"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('display_name') border-red-500 @enderror">
                @error('display_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Role (Slug)</label>
                <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required placeholder="e.g., kepala-gudang (tanpa spasi, huruf kecil)"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900">Hak Akses (Permissions)</h3>
                <p class="text-sm text-gray-500 mb-4">Pilih hak akses yang akan dimiliki oleh role ini.</p>
                <div class="space-y-4">
                    @php
                        $rolePermissions = $role->permissions->pluck('id')->toArray();
                    @endphp
                    @foreach ($permissions as $group => $permissionList)
                        <fieldset class="border p-4 rounded-md">
                            <legend class="px-2 font-semibold text-sm capitalize">{{ str_replace('-', ' ', $group) }}</legend>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mt-2">
                                @foreach ($permissionList as $permission)
                                    <label class="flex items-center space-x-3">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                               class="h-4 w-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500"
                                               {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
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
                Simpan Perubahan
            </button>
            <a href="{{ route('roles.index') }}" class="text-gray-600 font-semibold px-4 py-2 rounded-lg hover:bg-gray-200">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection