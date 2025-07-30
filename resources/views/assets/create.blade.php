@extends('layouts.app')

@section('title', 'Tambah Aset Baru')

@section('content')
    <form action="{{ route('assets.store') }}" method="POST">
        @csrf

        {{-- Page Header and Action Buttons --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Tambah Aset Baru</h1>
                <p class="text-sm text-gray-500">Isi detail aset yang akan ditambahkan.</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('assets.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors py-2 px-3 rounded-lg">Batal</a>
                <button type="submit" class="bg-emerald-600 text-white font-semibold px-5 py-2 rounded-lg hover:bg-emerald-700 transition-colors shadow-md">Simpan Aset</button>
            </div>
        </div>

        {{-- Error Messages Display --}}
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow" role="alert">
                <p class="font-bold">Terjadi Kesalahan</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Form Content with Alpine.js for conditional fields --}}
        <div class="space-y-8"
             x-data="{
                 asset_category: '{{ old('asset_category', 'ELEC') }}',
                 spec_input_type: '{{ old('spec_input_type', 'detailed') }}',
                 init() {
                     // On initial load, if the category is not 'ELEC', force spec_input_type to 'manual'
                     if (this.asset_category !== 'ELEC') {
                         this.spec_input_type = 'manual';
                     }
                 },
                 handleCategoryChange() {
                     // When asset_category changes, if it's not 'ELEC', force spec_input_type to 'manual'
                     if (this.asset_category !== 'ELEC') {
                         this.spec_input_type = 'manual';
                     }
                 }
             }">

            {{-- Informasi Utama Section --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Utama</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                    <div>
                        <label for="nama_barang" class="block text-sm font-medium text-gray-600">Nama Barang <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_barang" id="nama_barang" value="{{ old('nama_barang') }}" placeholder="Contoh: Laptop, Meja Kerja, AC Split" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3" required>
                    </div>
                    <div>
                        <label for="asset_category" class="block text-sm font-medium text-gray-600">Kategori Barang</label>
                        <select name="asset_category" id="asset_category" x-model="asset_category" @change="handleCategoryChange()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                            @foreach($assetCategories as $code => $name)
                                <option value="{{ $code }}" {{ old('asset_category') == $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="company_code" class="block text-sm font-medium text-gray-600">Kode Perusahaan</label>
                        <select name="company_code" id="company_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                            @foreach($companyCodes as $code => $name)
                                <option value="{{ $code }}" {{ old('company_code') == $code ? 'selected' : '' }}>{{ $code }} - {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="merk_type" class="block text-sm font-medium text-gray-600">Merk/Tipe</label>
                        <input type="text" name="merk_type" id="merk_type" value="{{ old('merk_type') }}" placeholder="Contoh: Asus ROG, IKEA, Daikin" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="serial_number" class="block text-sm font-medium text-gray-600">Serial Number</label>
                        <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}" placeholder="Contoh: ABC123XYZ789" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="kondisi" class="block text-sm font-medium text-gray-600">Kondisi</label>
                        <select name="kondisi" id="kondisi" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                            <option value="BAIK" {{ old('kondisi') == 'BAIK' ? 'selected' : '' }}>BAIK</option>
                            <option value="RUSAK" {{ old('kondisi') == 'RUSAK' ? 'selected' : '' }}>RUSAK</option>
                            <option value="DALAM PERBAIKAN" {{ old('kondisi') == 'DALAM PERBAIKAN' ? 'selected' : '' }}>DALAM PERBAIKAN</option>
                        </select>
                    </div>
                    <div>
                        <label for="lokasi" class="block text-sm font-medium text-gray-600">Lokasi Fisik</label>
                        <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: Gedung A Lantai 3, Ruang Server" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                </div>
            </div>

            {{-- Informasi Pengguna Section --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Pengguna</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                    <div>
                        <label for="new_user_name" class="block text-sm font-medium text-gray-600">Nama Pengguna</label>
                        <input type="text" name="new_user_name" id="new_user_name" value="{{ old('new_user_name') }}" placeholder="Nama Lengkap Pengguna" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="jabatan" class="block text-sm font-medium text-gray-600">Jabatan</label>
                        <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}" placeholder="Jabatan pengguna" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="departemen" class="block text-sm font-medium text-gray-600">Departemen</label>
                        <input type="text" name="departemen" id="departemen" value="{{ old('departemen') }}" placeholder="Departemen pengguna" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                </div>
            </div>

            {{-- Detail Spesifikasi & Pembelian Section --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Detail Spesifikasi & Pembelian</h3>

                {{-- Input Type Selection (only for 'ELEC' category) --}}
                <div class="mb-6" x-show="asset_category === 'ELEC'">
                    <label for="spec_input_type" class="block text-sm font-medium text-gray-600">Metode Input Spesifikasi</label>
                    <select x-model="spec_input_type" name="spec_input_type" id="spec_input_type" class="mt-1 block w-full md:w-1/3 border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                        <option value="detailed">Input Rinci (Processor, RAM, dll.)</option>
                        <option value="manual">Input Manual (Teks Bebas)</option>
                    </select>
                </div>

                {{-- Detailed Specification Form (only for 'ELEC' and 'detailed' input type) --}}
                <div x-show="asset_category === 'ELEC' && spec_input_type === 'detailed'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8">
                    <div>
                        <label for="processor" class="block text-sm font-medium text-gray-600">Processor</label>
                        <input type="text" name="processor" id="processor" value="{{ old('processor') }}" placeholder="Contoh: Intel Core i7, AMD Ryzen 5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="memory_ram" class="block text-sm font-medium text-gray-600">RAM</label>
                        <input type="text" name="memory_ram" id="memory_ram" value="{{ old('memory_ram') }}" placeholder="Contoh: 8GB DDR4, 16GB" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="hdd_ssd" class="block text-sm font-medium text-gray-600">Storage (HDD/SSD)</label>
                        <input type="text" name="hdd_ssd" id="hdd_ssd" value="{{ old('hdd_ssd') }}" placeholder="Contoh: 256GB SSD, 1TB HDD" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="graphics" class="block text-sm font-medium text-gray-600">Graphics</label>
                        <input type="text" name="graphics" id="graphics" value="{{ old('graphics') }}" placeholder="Contoh: NVIDIA GeForce RTX 3050, Intel Iris Xe" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="lcd" class="block text-sm font-medium text-gray-600">Layar (LCD)</label>
                        <input type="text" name="lcd" id="lcd" value="{{ old('lcd') }}" placeholder="Contoh: 14 inch FHD, 27 inch IPS" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                </div>

                {{-- Manual Specification Form (for non-ELEC or 'manual' input type) --}}
                <div x-show="asset_category !== 'ELEC' || spec_input_type === 'manual'">
                    <label for="spesifikasi_manual" class="block text-sm font-medium text-gray-600">Detail Spesifikasi / Deskripsi</label>
                    <textarea name="spesifikasi_manual" id="spesifikasi_manual" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3" placeholder="Jika aset bukan elektronik, masukkan deskripsi di sini.&#10;Contoh: Meja kayu jati, 2 laci, warna coklat.">{{ old('spesifikasi_manual') }}</textarea>
                </div>

                {{-- Purchase Information --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 mt-8 pt-6 border-t">
                    <div>
                        <label for="tanggal_pembelian" class="block text-sm font-medium text-gray-600">Tanggal Pembelian</label>
                        <input type="date" name="tanggal_pembelian" id="tanggal_pembelian" value="{{ old('tanggal_pembelian') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="thn_pembelian" class="block text-sm font-medium text-gray-600">Tahun Beli</label>
                        <input type="number" name="thn_pembelian" id="thn_pembelian" value="{{ old('thn_pembelian') }}" placeholder="Contoh: 2025" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="harga_total" class="block text-sm font-medium text-gray-600">Harga (Rp)</label>
                        <input type="number" name="harga_total" id="harga_total" value="{{ old('harga_total') }}" placeholder="Contoh: 15000000" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="code_aktiva" class="block text-sm font-medium text-gray-600">Kode Aktiva</label>
                        <input type="text" name="code_aktiva" id="code_aktiva" value="{{ old('code_aktiva') }}" placeholder="Contoh: A001" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="po_number" class="block text-sm font-medium text-gray-600">Nomor PO</label>
                        <input type="text" name="po_number" id="po_number" value="{{ old('po_number') }}" placeholder="Contoh: PO/2025/001" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="nomor" class="block text-sm font-medium text-gray-600">Nomor BAST</label>
                        <input type="text" name="nomor" id="nomor" value="{{ old('nomor') }}" placeholder="Contoh: BAST/2025/001" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="satuan" class="block text-sm font-medium text-gray-600">Jumlah</label>
                        <input type="number" name="satuan" id="satuan" value="{{ old('satuan', 1) }}" placeholder="Contoh: 1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                    </div>
                </div>
            </div>

            {{-- Informasi Tambahan Section --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Tambahan</h3>
                <div class="space-y-6">
                    <div>
                        <label for="include_items" class="block text-sm font-medium text-gray-600">Item Termasuk</label>
                        <textarea name="include_items" id="include_items" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3" placeholder="Contoh: Charger, Mouse, Tas Laptop">{{ old('include_items') }}</textarea>
                    </div>
                    <div>
                        <label for="peruntukan" class="block text-sm font-medium text-gray-600">Peruntukan</label>
                        <textarea name="peruntukan" id="peruntukan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3" placeholder="Contoh: Divisi IT, Proyek Pembangunan">{{ old('peruntukan') }}</textarea>
                    </div>
                    <div>
                        <label for="keterangan" class="block text-sm font-medium text-gray-600">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3" placeholder="Tambahkan catatan atau informasi penting lainnya di sini.">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
