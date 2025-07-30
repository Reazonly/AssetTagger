@extends('layouts.app')

@section('title', 'Tambah Aset Baru')

@section('content')
    <form action="{{ route('assets.store') }}" method="POST">
        @csrf

        {{-- Page Header and Action Buttons --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Tambah Aset Baru</h1>
                <p class="text-sm text-gray-500 mt-1">Isi detail aset yang akan ditambahkan.</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('assets.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors py-2 px-4 rounded-lg bg-gray-200 hover:bg-gray-300">Batal</a>
                <button type="submit" class="bg-emerald-600 text-white font-semibold px-5 py-2 rounded-lg hover:bg-emerald-700 transition-colors shadow-md">Simpan Aset</button>
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
                asset_category: '{{ old('asset_category', 'ELEC') }}', 
                spec_input_method: '{{ old('spec_input_type', 'detailed') }}'
             }">

            {{-- This hidden input is the single source of truth for spec_input_type --}}
            <input type="hidden" name="spec_input_type" :value="asset_category === 'ELEC' ? spec_input_method : 'manual'">

            {{-- Informasi Utama Section --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Utama</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                    <div>
                        <label for="nama_barang" class="block text-sm font-medium text-gray-600">Nama Barang <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_barang" id="nama_barang" value="{{ old('nama_barang') }}" placeholder="Contoh: Laptop, Meja Kerja" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3" required>
                    </div>
                    <div>
                        <label for="asset_category" class="block text-sm font-medium text-gray-600">Kategori Barang</label>
                        <select name="asset_category" id="asset_category" x-model="asset_category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                            @foreach($assetCategories as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="company_code" class="block text-sm font-medium text-gray-600">Kode Perusahaan</label>
                        <select name="company_code" id="company_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                            @foreach($companyCodes as $code => $name)
                                <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="merk_type" class="block text-sm font-medium text-gray-600">Merk/Tipe</label>
                        <input type="text" name="merk_type" id="merk_type" value="{{ old('merk_type') }}" placeholder="Contoh: Asus ROG, IKEA" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="serial_number" class="block text-sm font-medium text-gray-600">Serial Number</label>
                        <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}" placeholder="Contoh: ABC123XYZ789" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="kondisi" class="block text-sm font-medium text-gray-600">Kondisi</label>
                        <select name="kondisi" id="kondisi" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                            <option value="BAIK">BAIK</option>
                            <option value="RUSAK">RUSAK</option>
                            <option value="DALAM PERBAIKAN">DALAM PERBAIKAN</option>
                        </select>
                    </div>
                    <div>
                        <label for="lokasi" class="block text-sm font-medium text-gray-600">Lokasi Fisik</label>
                        <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: Gedung A Lantai 3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="jumlah" class="block text-sm font-medium text-gray-600">Jumlah</label>
                        <input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah', 1) }}" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="satuan" class="block text-sm font-medium text-gray-600">Satuan</label>
                        <select name="satuan" id="satuan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                            <option value="Unit">Unit</option>
                            <option value="Buah">Buah</option>
                            <option value="Pcs">Pcs</option>
                            <option value="Set">Set</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Informasi Pengguna Section --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Pengguna</h3>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-600">Pilih Pengguna (Jika Sudah Ada)</label>
                    <select name="user_id" id="user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <option value="">-- Tidak Ada Pengguna --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->nama_pengguna }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-4 text-center text-sm font-semibold text-gray-500">ATAU</div>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                    <div>
                        <label for="new_user_name" class="block text-sm font-medium text-gray-600">Tambah Pengguna Baru</label>
                        <input type="text" name="new_user_name" placeholder="Nama Lengkap Pengguna Baru" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="jabatan" class="block text-sm font-medium text-gray-600">Jabatan</label>
                        <input type="text" name="jabatan" placeholder="Jabatan pengguna baru" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="departemen" class="block text-sm font-medium text-gray-600">Departemen</label>
                        <input type="text" name="departemen" placeholder="Departemen pengguna baru" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                </div>
            </div>

            {{-- Detail Spesifikasi & Pembelian Section --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Detail Spesifikasi & Pembelian</h3>
                
                <div class="mb-6" x-show="asset_category === 'ELEC'">
                    <label for="spec_input_method_select" class="block text-sm font-medium text-gray-600">Metode Input Spesifikasi</label>
                    <select x-model="spec_input_method" id="spec_input_method_select" class="mt-1 block w-full md:w-1/3 border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <option value="detailed">Input Rinci</option>
                        <option value="manual">Input Manual (Teks)</option>
                    </select>
                </div>

                <div x-show="asset_category === 'ELEC' && spec_input_method === 'detailed'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8">
                    <div><label for="processor" class="block text-sm font-medium text-gray-600">Processor</label><input type="text" name="processor" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="memory_ram" class="block text-sm font-medium text-gray-600">RAM</label><input type="text" name="memory_ram" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="hdd_ssd" class="block text-sm font-medium text-gray-600">Storage</label><input type="text" name="hdd_ssd" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="graphics" class="block text-sm font-medium text-gray-600">Graphics</label><input type="text" name="graphics" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="lcd" class="block text-sm font-medium text-gray-600">Layar</label><input type="text" name="lcd" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                </div>

                <div x-show="asset_category !== 'ELEC' || spec_input_method === 'manual'">
                    <label for="spesifikasi_manual" class="block text-sm font-medium text-gray-600">Detail Spesifikasi / Deskripsi</label>
                    <textarea name="spesifikasi_manual" id="spesifikasi_manual" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3" placeholder="Jika aset bukan elektronik, masukkan deskripsi di sini.&#10;Contoh: Meja kayu jati, 2 laci, warna coklat."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8 mt-8 pt-6 border-t">
                    <div><label for="tanggal_pembelian" class="block text-sm font-medium text-gray-600">Tanggal Pembelian</label><input type="date" name="tanggal_pembelian" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="harga_total" class="block text-sm font-medium text-gray-600">Harga (Rp)</label><input type="number" name="harga_total" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="code_aktiva" class="block text-sm font-medium text-gray-600">Kode Aktiva</label><input type="text" name="code_aktiva" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="po_number" class="block text-sm font-medium text-gray-600">Nomor PO</label><input type="text" name="po_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                    <div><label for="nomor" class="block text-sm font-medium text-gray-600">Nomor BAST</label><input type="text" name="nomor" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></div>
                </div>
            </div>

            {{-- Informasi Tambahan Section --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Tambahan</h3>
                <div class="space-y-6">
                    <div><label for="include_items" class="block text-sm font-medium text-gray-600">Item Termasuk</label><textarea name="include_items" id="include_items" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></textarea></div>
                    <div><label for="peruntukan" class="block text-sm font-medium text-gray-600">Peruntukan</label><textarea name="peruntukan" id="peruntukan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></textarea></div>
                    <div><label for="keterangan" class="block text-sm font-medium text-gray-600">Keterangan</label><textarea name="keterangan" id="keterangan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3"></textarea></div>
                </div>
            </div>
        </div>
    </form>
@endsection