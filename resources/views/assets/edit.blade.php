@extends('layouts.app')
@section('title', 'Edit Aset - ' . $asset->code_asset)
@section('content')
    <form action="{{ route('assets.update', $asset->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Page Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Edit Aset</h1>
            <p class="text-sm text-gray-500 mt-1">Memperbarui data untuk: <span class="font-semibold text-emerald-600">{{ $asset->code_asset }}</span></p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow" role="alert">
                <p class="font-bold">Terjadi Kesalahan</p>
                <ul class="list-disc list-inside mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Form Content with Alpine.js --}}
        <div class="space-y-8" 
             x-data="{ 
                categoriesData: {{ Js::from($categories->keyBy('id')) }},
                assetUsersData: {{ Js::from($users->keyBy('id')) }},
                selectedCategoryId: {{ old('category_id', $asset->category_id) }},
                selectedSubCategoryId: null, // Dikosongkan dulu
                selectedAssetUserId: {{ old('asset_user_id', $asset->asset_user_id ?? 'null') }},

                // --- PERBAIKAN DI SINI ---
                init() {
                    // Fungsi ini akan berjalan setelah Alpine.js siap.
                    // Kita tunggu DOM selesai update, baru set nilainya.
                    this.$nextTick(() => {
                        this.selectedSubCategoryId = {{ old('sub_category_id', $asset->sub_category_id ?? 'null') }};
                    });
                },

                get currentCategory() {
                    return this.categoriesData[this.selectedCategoryId] || { sub_categories: [], units: [] };
                },
                get currentSubCategory() {
                    const subCategories = Array.isArray(this.currentCategory.sub_categories) ? this.currentCategory.sub_categories : Object.values(this.currentCategory.sub_categories);
                    return subCategories.find(sc => sc.id == this.selectedSubCategoryId) || {};
                },
                get currentAssetUser() {
                    return this.assetUsersData[this.selectedAssetUserId] || {};
                }
             }" x-init="init()">

            <!-- Informasi Utama -->
            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Utama</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600">Nama Barang / Tipe</label>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $asset->nama_barang }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Kategori</label>
                        <p class="mt-1 text-gray-800">{{ $asset->category->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Perusahaan</label>
                        <p class="mt-1 text-gray-800">{{ $asset->company->name }}</p>
                    </div>
                    <div x-show="Object.keys(currentCategory.sub_categories).length > 0">
                        <label for="sub_category_id" class="block text-sm font-medium text-gray-600">Sub Kategori</label>
                        <select name="sub_category_id" id="sub_category_id" x-model="selectedSubCategoryId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                            <option value="">-- Pilih Sub Kategori --</option>
                            <template x-for="subCategory in currentCategory.sub_categories" :key="subCategory.id">
                                <option :value="subCategory.id" x-text="subCategory.name"></option>
                            </template>
                        </select>
                    </div>
                    <div x-show="currentCategory.requires_merk">
                        <label for="merk" class="block text-sm font-medium text-gray-600">Merk</label>
                        <input type="text" name="merk" id="merk" value="{{ old('merk', $asset->merk) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div x-show="!currentCategory.requires_merk && currentCategory.code !== 'FURN'">
                        <label for="tipe" class="block text-sm font-medium text-gray-600">Tipe</label>
                        <input type="text" name="tipe" id="tipe" value="{{ old('tipe', $asset->tipe) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                </div>
            </div>

            <!-- Informasi Pengguna -->
            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Pengguna</h3>
                <div>
                    <label for="asset_user_id" class="block text-sm font-medium text-gray-700">Pilih Pengguna (Jika Ada)</label>
                    <select id="asset_user_id" name="asset_user_id" x-model="selectedAssetUserId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <option value="">-- Tidak ada pengguna --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="selectedAssetUserId" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-md border" x-cloak>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Jabatan</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="currentAssetUser.jabatan || '-'"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Departemen</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="currentAssetUser.departemen || '-'"></p>
                    </div>
                </div>
            </div>

            <!-- Detail & Spesifikasi -->
            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Detail & Spesifikasi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label for="serial_number" class="block text-sm font-medium text-gray-600">Serial Number</label><input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="kondisi" class="block text-sm font-medium text-gray-600">Kondisi</label><select name="kondisi" id="kondisi" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"><option {{ $asset->kondisi == 'Baik' ? 'selected' : '' }}>Baik</option><option {{ $asset->kondisi == 'Rusak' ? 'selected' : '' }}>Rusak</option><option {{ $asset->kondisi == 'Perbaikan' ? 'selected' : '' }}>Perbaikan</option></select></div>
                    <div><label for="jumlah" class="block text-sm font-medium text-gray-600">Jumlah</label><input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah', $asset->jumlah) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div>
                        <label for="satuan" class="block text-sm font-medium text-gray-600">Satuan</label>
                        <select name="satuan" id="satuan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                            <template x-for="unit in currentCategory.units" :key="unit.id">
                                <option :value="unit.name" x-text="unit.name" :selected="unit.name == '{{ old('satuan', $asset->satuan) }}'"></option>
                            </template>
                             <option value="Unit" x-show="currentCategory.units.length === 0" :selected="'Unit' == '{{ old('satuan', $asset->satuan) }}'">Unit</option>
                        </select>
                    </div>
                    <div class="md:col-span-2"><label for="lokasi" class="block text-sm font-medium text-gray-600">Lokasi Fisik</label><input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi', $asset->lokasi) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                </div>
                
                <div class="mt-8 pt-6 border-t">
                    <h4 class="text-lg font-medium text-gray-800 mb-4">Spesifikasi Detail</h4>
                    <div class="space-y-4">
                        <template x-if="['Laptop', 'Desktop/PC'].includes(currentSubCategory.name)">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div><label class="block text-sm">Processor</label><input type="text" name="spec[processor]" value="{{ old('spec.processor', $asset->specifications['processor'] ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md text-sm py-2 px-3"></div>
                                <div><label class="block text-sm">RAM</label><input type="text" name="spec[ram]" value="{{ old('spec.ram', $asset->specifications['ram'] ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md text-sm py-2 px-3"></div>
                                <div><label class="block text-sm">Storage</label><input type="text" name="spec[storage]" value="{{ old('spec.storage', $asset->specifications['storage'] ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md text-sm py-2 px-3"></div>
                                <div><label class="block text-sm">Graphics</label><input type="text" name="spec[graphics]" value="{{ old('spec.graphics', $asset->specifications['graphics'] ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md text-sm py-2 px-3"></div>
                            </div>
                        </template>
                        <template x-if="currentSubCategory.name === 'Monitor'">
                             <div><label class="block text-sm">Ukuran Layar (inch)</label><input type="text" name="spec[layar]" value="{{ old('spec.layar', $asset->specifications['layar'] ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md text-sm py-2 px-3"></div>
                        </template>
                        <template x-if="currentCategory.code === 'VEHI'">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div><label class="block text-sm">Nomor Polisi</label><input type="text" name="spec[nomor_polisi]" value="{{ old('spec.nomor_polisi', $asset->specifications['nomor_polisi'] ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md text-sm py-2 px-3"></div>
                                <div><label class="block text-sm">Nomor Rangka</label><input type="text" name="spec[nomor_rangka]" value="{{ old('spec.nomor_rangka', $asset->specifications['nomor_rangka'] ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md text-sm py-2 px-3"></div>
                                <div><label class="block text-sm">Nomor Mesin</label><input type="text" name="spec[nomor_mesin]" value="{{ old('spec.nomor_mesin', $asset->specifications['nomor_mesin'] ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md text-sm py-2 px-3"></div>
                            </div>
                        </template>
                        <div>
                            <label class="block text-sm">Deskripsi / Spesifikasi Lainnya</label>
                            <textarea name="spec[deskripsi]" rows="3" class="mt-1 w-full border-gray-300 rounded-md text-sm py-2 px-3">{{ old('spec.deskripsi', $asset->specifications['deskripsi'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Pembelian & Dokumen -->
            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Pembelian & Dokumen</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label for="tanggal_pembelian" class="block text-sm font-medium text-gray-600">Tanggal Pembelian</label><input type="date" name="tanggal_pembelian" id="tanggal_pembelian" value="{{ old('tanggal_pembelian', optional($asset->tanggal_pembelian)->format('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="harga_total" class="block text-sm font-medium text-gray-600">Harga Total (Rp)</label><input type="number" name="harga_total" id="harga_total" value="{{ old('harga_total', $asset->harga_total) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="po_number" class="block text-sm font-medium text-gray-600">Nomor PO</label><input type="text" name="po_number" id="po_number" value="{{ old('po_number', $asset->po_number) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="nomor" class="block text-sm font-medium text-gray-600">Nomor BAST</label><input type="text" name="nomor" id="nomor" value="{{ old('nomor', $asset->nomor) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="code_aktiva" class="block text-sm font-medium text-gray-600">Kode Aktiva</label><input type="text" name="code_aktiva" id="code_aktiva" value="{{ old('code_aktiva', $asset->code_aktiva) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="sumber_dana" class="block text-sm font-medium text-gray-600">Sumber Dana</label><input type="text" name="sumber_dana" id="sumber_dana" value="{{ old('sumber_dana', $asset->sumber_dana) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                </div>
            </div>

            <!-- Informasi Tambahan -->
            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Tambahan</h3>
                <div class="space-y-6">
                    <div><label for="include_items" class="block text-sm font-medium text-gray-600">Item Termasuk</label><textarea name="include_items" id="include_items" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">{{ old('include_items', $asset->include_items) }}</textarea></div>
                    <div><label for="peruntukan" class="block text-sm font-medium text-gray-600">Peruntukan</label><textarea name="peruntukan" id="peruntukan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">{{ old('peruntukan', $asset->peruntukan) }}</textarea></div>
                    <div><label for="keterangan" class="block text-sm font-medium text-gray-600">Keterangan</label><textarea name="keterangan" id="keterangan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">{{ old('keterangan', $asset->keterangan) }}</textarea></div>
                </div>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t flex justify-end items-center gap-3">
            <a href="{{ route('assets.index', $asset->id) }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors py-2 px-4 rounded-lg bg-gray-200 hover:bg-gray-300">Batal</a>
            <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700 shadow-sm transition-colors">Simpan Perubahan</button>
        </div>
    </form>
@endsection
