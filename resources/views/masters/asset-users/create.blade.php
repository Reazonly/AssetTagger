@extends('layouts.app')
@section('title', 'Tambah Pengguna Aset')
@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Tambah Pengguna Aset Baru</h1>
    <div class="bg-white p-8 rounded-lg shadow-md border">
        <form action="{{ route('master-data.asset-users.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium">Nama</label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="jabatan" class="block text-sm font-medium">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="departemen" class="block text-sm font-medium">Departemen</label>
                    <input type="text" name="departemen" id="departemen" value="{{ old('departemen') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>
            <div class="mt-8 flex gap-4">
                <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg">Simpan</button>
                <a href="{{ route('master-data.asset-users.index') }}" class="px-4 py-2 rounded-lg hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection