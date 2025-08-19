@extends('layouts.app')

@section('title', 'Tambah Aset Baru')

@section('content')
    <form action="{{ route('assets.store') }}" method="POST">
        @csrf
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Tambah Aset Baru</h1>
            <p class="text-sm text-gray-500 mt-1">Isi detail aset yang akan ditambahkan.</p>
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

        <div class="space-y-8" 
             x-data="{ 
                categoriesData: {{ Js::from($categories->keyBy('id')) }},
                assetUsersData: {{ Js::from($users->keyBy('id')) }},
                selectedCategoryId: {{ old('category_id') ?? 'null' }},
                selectedSubCategoryId: {{ old('sub_category_id') ?? 'null' }},
                selectedAssetUserId: {{ old('asset_user_id') ?? 'null' }},
                useMerk: {{ old('use_merk') ? 'true' : 'false' }},
                useTipe: {{ old('use_tipe') ? 'true' : 'false' }},
                
                get currentCategory() { return this.categoriesData[this.selectedCategoryId] || {}; },
                get currentAssetUser() { return this.assetUsersData[this.selectedAssetUserId] || {}; },
                
                get subCategories() { return this.currentCategory.sub_categories || []; },
                get currentSubCategory() { return this.subCategories.find(sc => sc.id == this.selectedSubCategoryId) || {}; },
                get hasSubCategories() { return this.subCategories.length > 0; },
                
                get isPrimaryInfoReady() {
                    if (!this.selectedCategoryId) return false;
                    return this.hasSubCategories ? !!this.selectedSubCategoryId : true;
                },
                
                get inputType() { return this.currentSubCategory.input_type || 'none'; }
             }"
             x-init="$watch('selectedCategoryId', () => { selectedSubCategoryId = null })">

            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Utama</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-600">Langkah 1: Pilih Kategori</label>
                        <select name="category_id" id="category_id" x-model="selectedCategoryId" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="hasSubCategories" x-cloak>
                        <label for="sub_category_id" class="block text-sm font-medium text-gray-600">Langkah 2: Pilih Sub Kategori</label>
                        <select name="sub_category_id" id="sub_category_id" x-model="selectedSubCategoryId" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">
                            <option value="">-- Pilih Sub Kategori --</option>
                            <template x-for="subCategory in subCategories" :key="subCategory.id">
                                <option :value="subCategory.id" x-text="subCategory.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div x-show="isPrimaryInfoReady" x-cloak class="mt-6 pt-6 border-t">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2"><label for="nama_barang" class="block text-sm font-medium text-gray-600">Nama Barang</label><input type="text" name="nama_barang" id="nama_barang" value="{{ old('nama_barang') }}" required class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                        
                        <div x-show="inputType === 'merk_dan_tipe'" class="md:col-span-2 flex items-center space-x-6">
                            <label class="flex items-center"><input type="checkbox" name="use_merk" x-model="useMerk" class="mr-2"> Input Merk</label>
                            <label class="flex items-center"><input type="checkbox" name="use_tipe" x-model="useTipe" class="mr-2"> Input Tipe</label>
                        </div>
                        
                        <div x-show="inputType === 'merk' || (inputType === 'merk_dan_tipe' && useMerk)"><label for="merk" class="block text-sm font-medium text-gray-600">Merk</label><input type="text" name="merk" id="merk" value="{{ old('merk') }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                        
                        <div x-show="inputType === 'tipe' || (inputType === 'merk_dan_tipe' && useTipe)"><label for="tipe" class="block text-sm font-medium text-gray-600">Tipe</label><input type="text" name="tipe" id="tipe" value="{{ old('tipe') }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                        
                        <div><label for="company_id" class="block text-sm font-medium text-gray-600">Perusahaan Pemilik</label><select name="company_id" id="company_id" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">@foreach($companies as $company)<option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>@endforeach</select></div>
                    </div>
                </div>
            </div>

            <div x-show="isPrimaryInfoReady" x-cloak class="space-y-8">

            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Pengguna</h3>
                <div>
                    <label for="asset_user_id" class="block text-sm font-medium text-gray-700">Pilih Pengguna</label>
                    <select id="asset_user_id" name="asset_user_id" x-model.number="selectedAssetUserId" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3" required>
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
                    <div><label for="serial_number" class="block text-sm font-medium text-gray-600">Serial Number</label><input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="kondisi" class="block text-sm font-medium text-gray-600">Kondisi</label><select name="kondisi" id="kondisi" required class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"><option value="Baik">Baik</option><option value="Rusak">Rusak</option><option value="Perbaikan">Perbaikan</option></select></div>
                    <div><label for="jumlah" class="block text-sm font-medium text-gray-600">Jumlah</label><input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah', 1) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="satuan" class="block text-sm font-medium text-gray-600">Satuan</label><select name="satuan" id="satuan" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"><template x-for="unit in currentCategory.units" :key="unit.id"><option :value="unit.name" x-text="unit.name"></option></template><option value="Unit" x-show="currentCategory.units.length === 0">Unit</option></select></div>
                    <div class="md:col-span-2"><label for="lokasi" class="block text-sm font-medium text-gray-600">Lokasi Fisik</label><input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                </div>
                
                <div class="mt-8 pt-6 border-t">
                    <h4 class="text-lg font-medium text-gray-800 mb-4">Spesifikasi Detail</h4>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <template x-if="currentSubCategory && currentSubCategory.spec_fields">
                                <template x-for="field in currentSubCategory.spec_fields" :key="field">
                                    <div>
                                        <label class="block text-sm" x-text="field"></label>
                                        <input type="text" :name="'spec[' + field.toLowerCase().replace(/ /g, '_') + ']'" 
                                               class="mt-1 w-full border-2 border-gray-400 rounded-md text-sm py-2 px-3">
                                    </div>
                                </template>
                            </template>
                        </div>
                                                
                        <div>
                            <label class="block text-sm">Deskripsi / Spesifikasi Tambahan</label>
                            <textarea name="spec[deskripsi]" rows="3" class="mt-1 w-full border-2 border-gray-400 rounded-md text-sm py-2 px-3"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Pembelian & Dokumen</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label for="tanggal_pembelian" class="block text-sm font-medium text-gray-600">Tanggal Pembelian</label><input type="date" name="tanggal_pembelian" id="tanggal_pembelian" value="{{ old('tanggal_pembelian') }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="harga_total" class="block text-sm font-medium text-gray-600">Harga Total (Rp)</label><input type="number" name="harga_total" id="harga_total" value="{{ old('harga_total') }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="po_number" class="block text-sm font-medium text-gray-600">Nomor PO</label><input type="text" name="po_number" id="po_number" value="{{ old('po_number') }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="nomor" class="block text-sm font-medium text-gray-600">Nomor BAST</label><input type="text" name="nomor" id="nomor" value="{{ old('nomor') }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="code_aktiva" class="block text-sm font-medium text-gray-600">Kode Aktiva</label><input type="text" name="code_aktiva" id="code_aktiva" value="{{ old('code_aktiva') }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="sumber_dana" class="block text-sm font-medium text-gray-600">Sumber Dana</label><input type="text" name="sumber_dana" id="sumber_dana" value="{{ old('sumber_dana') }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3"></div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Tambahan</h3>
                <div class="space-y-6">
                    <div><label for="include_items" class="block text-sm font-medium text-gray-600">Item Termasuk</label><textarea name="include_items" id="include_items" rows="3" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">{{ old('include_items') }}</textarea></div>
                    <div><label for="peruntukan" class="block text-sm font-medium text-gray-600">Peruntukan</label><textarea name="peruntukan" id="peruntukan" rows="3" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">{{ old('peruntukan') }}</textarea></div>
                    <div><label for="keterangan" class="block text-sm font-medium text-gray-600">Keterangan</label><textarea name="keterangan" id="keterangan" rows="3" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3">{{ old('keterangan') }}</textarea></div>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t flex justify-end items-center gap-3">
            <a href="{{ route('assets.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors py-2 px-4 rounded-lg bg-gray-200 hover:bg-gray-300 border border-black">Batal</a>
            <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700 shadow-sm transition-colors">Simpan Aset</button>
        </div>
    </form>
@endsection
