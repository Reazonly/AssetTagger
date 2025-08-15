@extends('layouts.app')
@section('title', 'Edit Aset - ' . $asset->code_asset)
@section('content')
    <form action="{{ route('assets.update', $asset->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Edit Aset</h1>
            <p class="text-sm text-gray-500 mt-1">Memperbarui data untuk: <span class="font-semibold text-emerald-600">{{ $asset->code_asset }}</span></p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow" role="alert">
                <p class="font-bold">Terjadi Kesalahan</p>
                <ul class="list-disc list-inside mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="space-y-8" 
             x-data="{ 
                assetUsersData: {{ Js::from($users->keyBy('id')) }},
                selectedAssetUserId: {{ old('asset_user_id', $asset->asset_user_id) ?? 'null' }},
                get currentAssetUser() { return this.assetUsersData[this.selectedAssetUserId] || {} }
             }">
            
            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Utama</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    {{-- PERUBAHAN DI SINI: Field diubah menjadi teks statis, kecuali Perusahaan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                        <p class="mt-1 text-lg text-gray-900 font-semibold">{{ $asset->nama_barang }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kategori</label>
                        <p class="mt-1 text-lg text-gray-900 font-semibold">{{ optional($asset->category)->name }}</p>
                    </div>

                    @if($asset->subCategory)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sub Kategori</label>
                        <p class="mt-1 text-lg text-gray-900 font-semibold">{{ optional($asset->subCategory)->name }}</p>
                    </div>
                    @endif

                    @if($asset->merk)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Merk</label>
                        <p class="mt-1 text-lg text-gray-900 font-semibold">{{ $asset->merk }}</p>
                    </div>
                    @endif

                    @if($asset->tipe)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipe</label>
                        <p class="mt-1 text-lg text-gray-900 font-semibold">{{ $asset->tipe }}</p>
                    </div>
                    @endif

                    <div>
                        <label for="company_id" class="block text-sm font-medium text-gray-700">Perusahaan Pemilik (Dapat Diubah)</label>
                        <select name="company_id" id="company_id" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id', $asset->company_id) == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Pengguna</h3>
                <div>
                    <label for="asset_user_id" class="block text-sm font-medium text-gray-700">Pilih Pengguna (Jika Ada)</label>
                    <select id="asset_user_id" name="asset_user_id" x-model.number="selectedAssetUserId" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">
                        <option value="">-- Tidak ada pengguna --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="selectedAssetUserId" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-md border" x-cloak>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Jabatan</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="currentAssetUser.jabatan || '-'"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Departemen</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="currentAssetUser.departemen || '-'"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Perusahaan</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="currentAssetUser.company ? currentAssetUser.company.name : '-'"></p>
                    </div>
                </div>
            </div>

             <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Detail & Spesifikasi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Sisanya tetap bisa diedit --}}
                    <div><label for="serial_number" class="block text-sm font-medium text-gray-700">Serial Number</label><input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="kondisi" class="block text-sm font-medium text-gray-700">Kondisi</label><select name="kondisi" id="kondisi" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"><option value="Baik" {{ old('kondisi', $asset->kondisi) == 'Baik' ? 'selected' : '' }}>Baik</option><option value="Rusak" {{ old('kondisi', $asset->kondisi) == 'Rusak' ? 'selected' : '' }}>Rusak</option><option value="Perbaikan" {{ old('kondisi', $asset->kondisi) == 'Perbaikan' ? 'selected' : '' }}>Perbaikan</option></select></div>
                    <div><label for="lokasi" class="block text-sm font-medium text-gray-700">Lokasi Fisik</label><input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi', $asset->lokasi) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah</label><input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah', $asset->jumlah) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label for="satuan" class="block text-sm font-medium text-gray-700">Satuan</label><input type="text" name="satuan" id="satuan" value="{{ old('satuan', $asset->satuan) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Pembelian & Dokumen</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label for="tanggal_pembelian" class="block text-sm font-medium text-gray-700">Tanggal Pembelian</label><input type="date" name="tanggal_pembelian" id="tanggal_pembelian" value="{{ old('tanggal_pembelian', optional($asset->tanggal_pembelian)->format('Y-m-d')) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="harga_total" class="block text-sm font-medium text-gray-700">Harga Total</label><input type="number" name="harga_total" id="harga_total" value="{{ old('harga_total', $asset->harga_total) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="po_number" class="block text-sm font-medium text-gray-700">Nomor PO</label><input type="text" name="po_number" id="po_number" value="{{ old('po_number', $asset->po_number) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="nomor" class="block text-sm font-medium text-gray-700">Nomor BAST</label><input type="text" name="nomor" id="nomor" value="{{ old('nomor', $asset->nomor) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="code_aktiva" class="block text-sm font-medium text-gray-700">Kode Aktiva</label><input type="text" name="code_aktiva" id="code_aktiva" value="{{ old('code_aktiva', $asset->code_aktiva) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="sumber_dana" class="block text-sm font-medium text-gray-700">Sumber Dana</label><input type="text" name="sumber_dana" id="sumber_dana" value="{{ old('sumber_dana', $asset->sumber_dana) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Tambahan</h3>
                <div class="space-y-6">
                    <div><label for="include_items" class="block text-sm font-medium text-gray-600">Item Termasuk</label><textarea name="include_items" id="include_items" rows="3" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">{{ old('include_items', $asset->include_items) }}</textarea></div>
                    <div><label for="peruntukan" class="block text-sm font-medium text-gray-600">Peruntukan</label><textarea name="peruntukan" id="peruntukan" rows="3" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">{{ old('peruntukan', $asset->peruntukan) }}</textarea></div>
                    <div><label for="keterangan" class="block text-sm font-medium text-gray-600">Keterangan</label><textarea name="keterangan" id="keterangan" rows="3" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">{{ old('keterangan', $asset->keterangan) }}</textarea></div>
                </div>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t flex justify-end items-center gap-3">
            <a href="{{ route('assets.show', $asset->id) }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors py-2 px-4 rounded-lg bg-gray-200 hover:bg-gray-300">Batal</a>
            <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700">Simpan Perubahan</button>
        </div>
    </form>
@endsection