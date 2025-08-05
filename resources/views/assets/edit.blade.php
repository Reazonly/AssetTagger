@extends('layouts.app')
@section('title', 'Edit Aset - ' . $asset->code_asset)
@section('content')
    <form action="{{ route('assets.update', $asset->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Edit Aset</h1>
                <p class="text-sm text-gray-500 mt-1">Memperbarui data untuk: <span class="font-semibold text-emerald-600">{{ $asset->code_asset }}</span></p>
            </div>
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
                selectedCategoryId: {{ $asset->category_id }},
                selectedSubCategoryId: null,
                
                categories: {{ $categories->keyBy('id')->map(function($c) {
                    return [
                        'id' => $c->id, 
                        'code' => $c->code, 
                        'requires_merk' => $c->requires_merk, 
                        'units' => $c->units,
                        'subCategories' => $c->subCategories
                    ];
                }) }},
                
                availableUnits: [],
                availableSubCategories: [],
                
                // --- PERBAIKAN FINAL ADA DI SINI ---
                init() {
                    // 1. Muat dulu daftar pilihan sub-kategori ke dalam variabel
                    this.updateDynamicFields();

                    // 2. Tunggu hingga DOM (tampilan) selesai diperbarui dengan daftar pilihan
                    this.$nextTick(() => {
                        // 3. Setelah daftar pilihan siap, BARU atur nilai yang seharusnya terpilih
                        this.selectedSubCategoryId = @json(old('sub_category_id', $asset->sub_category_id));
                    });
                },

                updateDynamicFields() {
                    const category = this.categories[this.selectedCategoryId];
                    if (category) {
                        this.availableUnits = category.units;
                        this.availableSubCategories = category.subCategories;
                    } else {
                        this.availableUnits = [];
                        this.availableSubCategories = [];
                    }
                },

                getCurrentCategoryCode() {
                    return this.categories[this.selectedCategoryId]?.code || '';
                },

                getSelectedSubCategoryName() {
                    if (!this.selectedSubCategoryId) return '';
                    const subCategory = this.availableSubCategories.find(sc => sc.id == this.selectedSubCategoryId);
                    return subCategory ? subCategory.name : '';
                }
             }">

            {{-- Informasi Utama Section --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Utama</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nama Barang</label>
                        <input type="text" value="{{ $asset->nama_barang }}" class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm py-2 px-3 cursor-not-allowed" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Kategori Barang</label>
                        <input type="text" value="{{ optional($asset->category)->name }}" class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm py-2 px-3 cursor-not-allowed" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Kode Perusahaan</label>
                        <input type="text" value="{{ optional($asset->company)->name }}" class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm py-2 px-3 cursor-not-allowed" readonly>
                    </div>
                    
                    <div x-show="categories[selectedCategoryId]?.requires_merk">
                        <label for="merk" class="block text-sm font-medium text-gray-600">Merk</label>
                        <input type="text" name="merk" id="merk" value="{{ old('merk', $asset->merk) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div x-show="!categories[selectedCategoryId]?.requires_merk">
                        <label for="tipe" class="block text-sm font-medium text-gray-600">Tipe</label>
                        <input type="text" name="tipe" id="tipe" value="{{ old('tipe', $asset->tipe) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="serial_number" class="block text-sm font-medium text-gray-600">Serial Number</label>
                        <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="kondisi" class="block text-sm font-medium text-gray-600">Kondisi</label>
                        <select name="kondisi" id="kondisi" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                            <option value="BAIK" @if(old('kondisi', $asset->kondisi) == 'BAIK') selected @endif>BAIK</option>
                            <option value="RUSAK" @if(old('kondisi', $asset->kondisi) == 'RUSAK') selected @endif>RUSAK</option>
                            <option value="DALAM PERBAIKAN" @if(old('kondisi', $asset->kondisi) == 'DALAM PERBAIKAN') selected @endif>DALAM PERBAIKAN</option>
                        </select>
                    </div>
                    <div>
                        <label for="lokasi" class="block text-sm font-medium text-gray-600">Lokasi Fisik</label>
                        <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi', $asset->lokasi) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="jumlah" class="block text-sm font-medium text-gray-600">Jumlah</label>
                        <input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah', $asset->jumlah) }}" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="satuan" class="block text-sm font-medium text-gray-600">Satuan</label>
                        <select name="satuan" id="satuan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                            <template x-for="unit in availableUnits" :key="unit.id">
                                <option :value="unit.name" x-text="unit.name" :selected="unit.name == '{{ old('satuan', $asset->satuan) }}'"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Pengguna</h3>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-600">Pilih Pengguna (Jika Sudah Ada)</label>
                    <select name="user_id" id="user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <option value="">-- Tidak Ada Pengguna --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $asset->user_id) == $user->id ? 'selected' : '' }}>{{ $user->nama_pengguna }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-4 text-center text-sm font-semibold text-gray-500">ATAU</div>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                    <div>
                        <label for="new_user_name" class="block text-sm font-medium text-gray-600">Tambah Pengguna Baru</label>
                        <input type="text" name="new_user_name" id="new_user_name" value="{{ old('new_user_name') }}" placeholder="Nama Lengkap Pengguna Baru" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="jabatan" class="block text-sm font-medium text-gray-600">Jabatan</label>
                        <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}" placeholder="Jabatan pengguna baru" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="departemen" class="block text-sm font-medium text-gray-600">Departemen</label>
                        <input type="text" name="departemen" id="departemen" value="{{ old('departemen') }}" placeholder="Departemen pengguna baru" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Detail Spesifikasi & Pembelian</h3>
                
                <div x-show="['ELEC', 'VEHI'].includes(getCurrentCategoryCode())" class="mb-8">
                    <label for="sub_category_id" class="block text-sm font-medium text-gray-600">Jenis Barang / Sub Kategori</label>
                    <select name="sub_category_id" id="sub_category_id" x-model="selectedSubCategoryId" class="mt-1 block w-full md:w-1/3 border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <option value="">-- Pilih Jenis Barang --</option>
                        <template x-for="sub in availableSubCategories" :key="sub.id">
                            <option :value="sub.id" x-text="sub.name"></option>
                        </template>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8 mt-8 pt-6 border-t">
                    <div><label for="tanggal_pembelian" class="block text-sm font-medium text-gray-600">Tanggal Pembelian</label><input type="date" name="tanggal_pembelian" value="{{ old('tanggal_pembelian', optional($asset->tanggal_pembelian)->format('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="harga_total" class="block text-sm font-medium text-gray-600">Harga (Rp)</label><input type="number" name="harga_total" value="{{ old('harga_total', $asset->harga_total) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="code_aktiva" class="block text-sm font-medium text-gray-600">Kode Aktiva</label><input type="text" name="code_aktiva" value="{{ old('code_aktiva', $asset->code_aktiva) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="po_number" class="block text-sm font-medium text-gray-600">Nomor PO</label><input type="text" name="po_number" value="{{ old('po_number', $asset->po_number) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="nomor" class="block text-sm font-medium text-gray-600">Nomor BAST</label><input type="text" name="nomor" value="{{ old('nomor', $asset->nomor) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="sumber_dana" class="block text-sm font-medium text-gray-600">Sumber Dana</label><input type="text" name="sumber_dana" value="{{ old('sumber_dana', $asset->sumber_dana) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Tambahan</h3>
                <div class="space-y-6">
                    <div><label for="include_items" class="block text-sm font-medium text-gray-600">Item Termasuk</label><textarea name="include_items" id="include_items" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">{{ old('include_items', $asset->include_items) }}</textarea></div>
                    <div><label for="peruntukan" class="block text-sm font-medium text-gray-600">Peruntukan</label><textarea name="peruntukan" id="peruntukan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">{{ old('peruntukan', $asset->peruntukan) }}</textarea></div>
                    <div><label for="keterangan" class="block text-sm font-medium text-gray-600">Keterangan</label><textarea name="keterangan" id="keterangan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">{{ old('keterangan', $asset->keterangan) }}</textarea></div>
                </div>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t flex justify-end items-center gap-3">
            <a href="{{ route('assets.show', $asset->id) }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors py-2 px-4 rounded-lg bg-gray-200 hover:bg-gray-300">Batal</a>
            <button type="submit" class="bg-emerald-600 text-white font-semibold px-5 py-2 rounded-lg hover:bg-emerald-700 transition-colors shadow-md">Simpan Perubahan</button>
        </div>
    </form>
@endsection