@extends('layouts.app')

@section('title', 'Tambah Aset Baru')

@section('content')
    <form action="{{ route('assets.store') }}" method="POST">
        @csrf

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Tambah Aset Baru</h1>
                <p class="text-sm text-gray-500 mt-1">Isi detail aset yang akan ditambahkan.</p>
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
                selectedCategoryId: {{ old('category_id', $categories->first()->id ?? 0) }},
                selectedSubCategoryId: {{ old('sub_category_id') ?? 'null' }},
                
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
                
                init() {
                    this.updateDynamicFields();
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
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Utama</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                    <div>
                        <label for="nama_barang" class="block text-sm font-medium text-gray-600">Nama Barang <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_barang" id="nama_barang" value="{{ old('nama_barang') }}" placeholder="Contoh: Laptop, Meja Kerja" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3" required>
                    </div>
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-600">Kategori Barang</label>
                        <select name="category_id" id="category_id" x-model="selectedCategoryId" @change="updateDynamicFields()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="company_id" class="block text-sm font-medium text-gray-600">Kode Perusahaan</label>
                        <select name="company_id" id="company_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->code }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div x-show="categories[selectedCategoryId]?.requires_merk">
                        <label for="merk" class="block text-sm font-medium text-gray-600">Merk</label>
                        <input type="text" name="merk" id="merk" value="{{ old('merk') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>

                    <div x-show="!categories[selectedCategoryId]?.requires_merk">
                        <label for="tipe" class="block text-sm font-medium text-gray-600">Tipe</label>
                        <input type="text" name="tipe" id="tipe" value="{{ old('tipe') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>

                    <div>
                        <label for="serial_number" class="block text-sm font-medium text-gray-600">Serial Number</label>
                        <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="kondisi" class="block text-sm font-medium text-gray-600">Kondisi</label>
                        <select name="kondisi" id="kondisi" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                            <option value="BAIK" @if(old('kondisi') == 'BAIK') selected @endif>BAIK</option>
                            <option value="RUSAK" @if(old('kondisi') == 'RUSAK') selected @endif>RUSAK</option>
                            <option value="DALAM PERBAIKAN" @if(old('kondisi') == 'DALAM PERBAIKAN') selected @endif>DALAM PERBAIKAN</option>
                        </select>
                    </div>
                    <div>
                        <label for="lokasi" class="block text-sm font-medium text-gray-600">Lokasi Fisik</label>
                        <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="jumlah" class="block text-sm font-medium text-gray-600">Jumlah</label>
                        <input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah', 1) }}" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="satuan" class="block text-sm font-medium text-gray-600">Satuan</label>
                        <select name="satuan" id="satuan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                            <template x-for="unit in availableUnits" :key="unit.id">
                                <option :value="unit.name" x-text="unit.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>
            
            {{-- Informasi Pengguna Section (Kode tidak berubah, tetap sama) --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Pengguna</h3>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-600">Pilih Pengguna (Jika Sudah Ada)</label>
                    <select name="user_id" id="user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <option value="">-- Tidak Ada Pengguna --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->nama_pengguna }}</option>
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
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Detail Spesifikasi & Pembelian</h3>
                
                <div x-show="['ELEC', 'VEHI'].includes(getCurrentCategoryCode())" class="mb-8">
                    <label for="sub_category_id" class="block text-sm font-medium text-gray-600">Jenis Barang / Sub Kategori</label>
                    <select name="sub_category_id" id="sub_category_id" x-model="selectedSubCategoryId" class="mt-1 block w-full md:w-1/3 border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <option value="">-- Pilih Jenis Barang --</option>
                        <template x-for="sub in availableSubCategories" :key="sub.id">
                            <option :value="sub.id" x-text="sub.name"></option>
                        </template>
                    </select>
                </div>

                {{-- OPSI UNTUK KATEGORI ELEKTRONIK --}}
                <div x-show="getCurrentCategoryCode() === 'ELEC'" class="space-y-6">
                    {{-- Spesifikasi Laptop & PC --}}
                    <div x-show="getSelectedSubCategoryName() === 'Laptop' || getSelectedSubCategoryName() === 'Desktop/PC'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                        <div><label class="block text-sm font-medium text-gray-600">Processor</label><input type="text" name="spec[processor]" value="{{ old('spec.processor') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">RAM</label><input type="text" name="spec[ram]" value="{{ old('spec.ram') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Storage</label><input type="text" name="spec[storage]" value="{{ old('spec.storage') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Graphics</label><input type="text" name="spec[graphics]" value="{{ old('spec.graphics') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Layar</label><input type="text" name="spec[layar]" value="{{ old('spec.layar') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    </div>

                    {{-- Spesifikasi Printer --}}
                    <div x-show="getSelectedSubCategoryName() === 'Printer'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                        <div><label class="block text-sm font-medium text-gray-600">Tipe Printer</label><input type="text" name="spec[tipe_printer]" value="{{ old('spec.tipe_printer') }}" placeholder="Inkjet, Laser" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Kecepatan Cetak</label><input type="text" name="spec[kecepatan_cetak]" value="{{ old('spec.kecepatan_cetak') }}" placeholder="Contoh: 20 ppm" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Resolusi Cetak</label><input type="text" name="spec[resolusi_cetak]" value="{{ old('spec.resolusi_cetak') }}" placeholder="Contoh: 1200 dpi" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Konektivitas</label><input type="text" name="spec[konektivitas]" value="{{ old('spec.konektivitas') }}" placeholder="USB, Wi-Fi, LAN" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    </div>

                    {{-- Spesifikasi Proyektor --}}
                    <div x-show="getSelectedSubCategoryName() === 'Proyektor'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                        <div><label class="block text-sm font-medium text-gray-600">Teknologi</label><input type="text" name="spec[teknologi]" value="{{ old('spec.teknologi') }}" placeholder="DLP, LCD" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Kecerahan (Lumens)</label><input type="text" name="spec[kecerahan]" value="{{ old('spec.kecerahan') }}" placeholder="Contoh: 3000" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Resolusi</label><input type="text" name="spec[resolusi]" value="{{ old('spec.resolusi') }}" placeholder="Contoh: 1920x1080" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    </div>
                    
                    {{-- Spesifikasi Monitor --}}
                    <div x-show="getSelectedSubCategoryName() === 'Monitor'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                        <div><label class="block text-sm font-medium text-gray-600">Ukuran Layar</label><input type="text" name="spec[ukuran_layar]" value="{{ old('spec.ukuran_layar') }}" placeholder="Contoh: 24 inch" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Resolusi</label><input type="text" name="spec[resolusi_monitor]" value="{{ old('spec.resolusi_monitor') }}" placeholder="Contoh: 1920x1080" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Refresh Rate</label><input type="text" name="spec[refresh_rate]" value="{{ old('spec.refresh_rate') }}" placeholder="Contoh: 75 Hz" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    </div>

                    {{-- Spesifikasi Lainnya (Elektronik) --}}
                    <div x-show="getSelectedSubCategoryName().includes('Lainnya')">
                        <label class="block text-sm font-medium text-gray-600">Tuliskan Spesifikasi Lainnya</label>
                        <textarea name="spec[lainnya]" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3" placeholder="Tuliskan detail spesifikasi untuk barang ini...">{{ old('spec.lainnya') }}</textarea>
                    </div>
                </div>

                {{-- OPSI UNTUK KATEGORI KENDARAAN --}}
                <div x-show="getCurrentCategoryCode() === 'VEHI'" class="space-y-6">
                     <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                        <div><label class="block text-sm font-medium text-gray-600">Nomor Polisi</label><input type="text" name="spec[nomor_polisi]" value="{{ old('spec.nomor_polisi') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Nomor Rangka</label><input type="text" name="spec[nomor_rangka]" value="{{ old('spec.nomor_rangka') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Nomor Mesin</label><input type="text" name="spec[nomor_mesin]" value="{{ old('spec.nomor_mesin') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Tipe Mesin</label><input type="text" name="spec[tipe_mesin]" value="{{ old('spec.tipe_mesin') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">CC Mesin</label><input type="text" name="spec[cc_mesin]" value="{{ old('spec.cc_mesin') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                        <div><label class="block text-sm font-medium text-gray-600">Bahan Bakar</label><input type="text" name="spec[bahan_bakar]" value="{{ old('spec.bahan_bakar') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    </div>
                </div>

                {{-- OPSI UNTUK KATEGORI LAIN (FURNITURE, DLL) --}}
                <div x-show="!['ELEC', 'VEHI'].includes(getCurrentCategoryCode())">
                    <label class="block text-sm font-medium text-gray-600">Deskripsi / Spesifikasi</label>
                    <textarea name="spec[deskripsi]" rows="5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3" placeholder="Contoh: Bahan Jati, Ukuran 200x80cm, Warna Coklat Tua">{{ old('spec.deskripsi') }}</textarea>
                </div>

                {{-- Informasi Pembelian (Tidak berubah) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8 mt-8 pt-6 border-t">
                    <div><label for="tanggal_pembelian" class="block text-sm font-medium text-gray-600">Tanggal Pembelian</label><input type="date" name="tanggal_pembelian" value="{{ old('tanggal_pembelian') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="harga_total" class="block text-sm font-medium text-gray-600">Harga (Rp)</label><input type="number" name="harga_total" value="{{ old('harga_total') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="code_aktiva" class="block text-sm font-medium text-gray-600">Kode Aktiva</label><input type="text" name="code_aktiva" value="{{ old('code_aktiva') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="po_number" class="block text-sm font-medium text-gray-600">Nomor PO</label><input type="text" name="po_number" value="{{ old('po_number') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="nomor" class="block text-sm font-medium text-gray-600">Nomor BAST</label><input type="text" name="nomor" value="{{ old('nomor') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                </div>
            </div>
            
             {{-- Informasi Tambahan Section (Kode tidak berubah, tetap sama) --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Tambahan</h3>
                <div class="space-y-6">
                    <div><label for="include_items" class="block text-sm font-medium text-gray-600">Item Termasuk</label><textarea name="include_items" id="include_items" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">{{ old('include_items') }}</textarea></div>
                    <div><label for="peruntukan" class="block text-sm font-medium text-gray-600">Peruntukan</label><textarea name="peruntukan" id="peruntukan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">{{ old('peruntukan') }}</textarea></div>
                    <div><label for="keterangan" class="block text-sm font-medium text-gray-600">Keterangan</label><textarea name="keterangan" id="keterangan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">{{ old('keterangan') }}</textarea></div>
                </div>
            </div>
        </div>

        {{-- Tombol Aksi di Bawah --}}
        <div class="mt-8 pt-6 border-t flex justify-end items-center gap-3">
            <a href="{{ route('assets.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors py-2 px-4 rounded-lg bg-gray-200 hover:bg-gray-300">Batal</a>
            <button type="submit" class="bg-emerald-600 text-white font-semibold px-5 py-2 rounded-lg hover:bg-emerald-700 transition-colors shadow-md">Simpan Aset</button>
        </div>
    </form>
@endsection