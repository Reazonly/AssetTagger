@extends('layouts.app')
@section('title', 'Edit Aset - ' . $asset->code_asset)
@section('content')
    {{-- Alpine.js diinisialisasi dengan data aset saat ini --}}
    <div x-data="{ spec_input_type: '{{ old('spec_input_type', $asset->spec_input_type) }}' }">
        <form action="{{ route('assets.update', $asset->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="flex justify-between items-center mb-6">
                <div><h1 class="text-3xl font-bold text-gray-800">Edit Aset</h1><p class="text-sm text-gray-500">Memperbarui data untuk: <span class="font-semibold text-emerald-600">{{ $asset->code_asset }}</span></p></div>
                <div><a href="{{ route('assets.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 mr-4">Batal</a><button type="submit" class="bg-emerald-600 text-white font-semibold px-5 py-2 rounded-lg hover:bg-emerald-700 transition-colors">Simpan Perubahan</button></div>
            </div>

            @if ($errors->any())<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert"><p class="font-bold">Terjadi Kesalahan</p><ul>@foreach ($errors->all() as $error)<li>- {{ $error }}</li>@endforeach</ul></div>@endif

            <div class="space-y-8">
                {{-- Informasi Utama --}}
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Utama</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Kode Aset</label>
                        <div class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm py-2 px-3 text-gray-500">
                            {{ $asset->code_asset }}
                        </div>
                    </div>
                    <div><label for="nama_barang" class="block text-sm font-medium text-gray-600">Nama Barang</label><input type="text" name="nama_barang" id="nama_barang" value="{{ old('nama_barang', $asset->nama_barang) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></div>
                    <div><label for="merk_type" class="block text-sm font-medium text-gray-600">Merk/Tipe</label><input type="text" name="merk_type" id="merk_type" value="{{ old('merk_type', $asset->merk_type) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></div>
                    <div><label for="serial_number" class="block text-sm font-medium text-gray-600">Serial Number</label><input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></div>
                    <div><label for="kondisi" class="block text-sm font-medium text-gray-600">Kondisi</label><select name="kondisi" id="kondisi" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"><option value="BAIK" {{ old('kondisi', $asset->kondisi) == 'BAIK' ? 'selected' : '' }}>BAIK</option><option value="RUSAK" {{ old('kondisi', $asset->kondisi) == 'RUSAK' ? 'selected' : '' }}>RUSAK</option><option value="DALAM PERBAIKAN" {{ old('kondisi', $asset->kondisi) == 'DALAM PERBAIKAN' ? 'selected' : '' }}>DALAM PERBAIKAN</option></select></div>
                    <div><label for="lokasi" class="block text-sm font-medium text-gray-600">Lokasi Fisik</label><input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi', $asset->lokasi) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></div>
                    
                    {{-- FIELD JUMLAH & SATUAN YANG DITAMBAHKAN --}}
                    <div>
                        <label for="jumlah" class="block text-sm font-medium text-gray-600">Jumlah</label>
                        <input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah', $asset->jumlah) }}" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="satuan" class="block text-sm font-medium text-gray-600">Satuan</label>
                        <select name="satuan" id="satuan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="Unit" @if(old('satuan', $asset->satuan) == 'Unit') selected @endif>Unit</option>
                            <option value="Buah" @if(old('satuan', $asset->satuan) == 'Buah') selected @endif>Buah</option>
                            <option value="Pcs" @if(old('satuan', $asset->satuan) == 'Pcs') selected @endif>Pcs</option>
                            <option value="Set" @if(old('satuan', $asset->satuan) == 'Set') selected @endif>Set</option>
                        </select>
                    </div>
                </div>
            </div>

                {{-- Informasi Pengguna --}}
                <div class="bg-white p-6 rounded-lg border shadow-sm">
                    <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Pengguna</h3>
                    <div><label for="user_id" class="block text-sm font-medium text-gray-600">Pilih Pengguna Saat Ini</label><select name="user_id" id="user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"><option value="">-- Tidak Ada Pengguna --</option>@foreach($users as $user)<option value="{{ $user->id }}" {{ old('user_id', $asset->user_id) == $user->id ? 'selected' : '' }}>{{ $user->nama_pengguna }}</option>@endforeach</select></div>
                    <div class="mt-4 text-center text-sm font-semibold text-gray-500">ATAU</div>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                        <div><label for="new_user_name" class="block text-sm font-medium text-gray-600">Tambah Pengguna Baru</label><input type="text" name="new_user_name" id="new_user_name" placeholder="Nama Lengkap" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                        <div><label for="jabatan" class="block text-sm font-medium text-gray-600">Jabatan</label><input type="text" name="jabatan" id="jabatan" placeholder="Jabatan pengguna baru" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                        <div><label for="departemen" class="block text-sm font-medium text-gray-600">Departemen</label><input type="text" name="departemen" id="departemen" placeholder="Departemen pengguna baru" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                    </div>
                </div>

                {{-- Detail Spesifikasi & Pembelian --}}
                <div class="bg-white p-6 rounded-lg border shadow-sm">
                    <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Detail Spesifikasi & Pembelian</h3>
                    
                    <div class="mb-6">
                        <label for="spec_input_type" class="block text-sm font-medium text-gray-600">Metode Input Spesifikasi</label>
                        <select x-model="spec_input_type" name="spec_input_type" id="spec_input_type" class="mt-1 block w-full md:w-1/3 border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                            <option value="detailed">Input Rinci</option>
                            <option value="manual">Input Manual (Teks)</option>
                        </select>
                    </div>

                    <div x-show="spec_input_type === 'detailed'" class="grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-8">
                        <div><label for="processor" class="block text-sm font-medium text-gray-600">Processor</label><input type="text" name="processor" id="processor" value="{{ old('processor', $asset->processor) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                        <div><label for="memory_ram" class="block text-sm font-medium text-gray-600">RAM</label><input type="text" name="memory_ram" id="memory_ram" value="{{ old('memory_ram', $asset->memory_ram) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                        <div><label for="hdd_ssd" class="block text-sm font-medium text-gray-600">Storage (HDD/SSD)</label><input type="text" name="hdd_ssd" id="hdd_ssd" value="{{ old('hdd_ssd', $asset->hdd_ssd) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                        <div><label for="graphics" class="block text-sm font-medium text-gray-600">Graphics</label><input type="text" name="graphics" id="graphics" value="{{ old('graphics', $asset->graphics) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                        <div><label for="lcd" class="block text-sm font-medium text-gray-600">Layar (LCD)</label><input type="text" name="lcd" id="lcd" value="{{ old('lcd', $asset->lcd) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                    </div>

                    <div x-show="spec_input_type === 'manual'">
                        <label for="spesifikasi_manual" class="block text-sm font-medium text-gray-600">Detail Spesifikasi (Manual)</label>
                        <textarea name="spesifikasi_manual" id="spesifikasi_manual" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">{{ old('spesifikasi_manual', $asset->spesifikasi_manual) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-8 mt-8 pt-6 border-t">
                        <div><label for="tanggal_pembelian" class="block text-sm font-medium text-gray-600">Tanggal Pembelian</label><input type="date" name="tanggal_pembelian" id="tanggal_pembelian" value="{{ old('tanggal_pembelian', optional($asset->tanggal_pembelian)->format('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                        <div><label for="thn_pembelian" class="block text-sm font-medium text-gray-600">Tahun Beli</label><input type="number" name="thn_pembelian" id="thn_pembelian" value="{{ old('thn_pembelian', $asset->thn_pembelian) }}" placeholder="2025" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                        <div><label for="harga_total" class="block text-sm font-medium text-gray-600">Harga</label><input type="number" name="harga_total" id="harga_total" value="{{ old('harga_total', $asset->harga_total) }}" placeholder="15000000" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                        <div><label for="code_aktiva" class="block text-sm font-medium text-gray-600">Kode Aktiva</label><input type="text" name="code_aktiva" id="code_aktiva" value="{{ old('code_aktiva', $asset->code_aktiva) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                        <div><label for="po_number" class="block text-sm font-medium text-gray-600">Nomor PO</label><input type="text" name="po_number" id="po_number" value="{{ old('po_number', $asset->po_number) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                        <div><label for="nomor" class="block text-sm font-medium text-gray-600">Nomor BAST</label><input type="text" name="nomor" id="nomor" value="{{ old('nomor', $asset->nomor) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></div>
                    </div>
                </div>
                
                {{-- Informasi Tambahan --}}
                <div class="bg-white p-6 rounded-lg border shadow-sm">
                    <h3 class="text-xl font-semibold border-b pb-3 mb-6 text-gray-700">Informasi Tambahan</h3>
                    <div class="space-y-6">
                        <div><label for="include_items" class="block text-sm font-medium text-gray-600">Item Termasuk</label><textarea name="include_items" id="include_items" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">{{ old('include_items', $asset->include_items) }}</textarea></div>
                        <div><label for="peruntukan" class="block text-sm font-medium text-gray-600">Peruntukan</label><textarea name="peruntukan" id="peruntukan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">{{ old('peruntukan', $asset->peruntukan) }}</textarea></div>
                        <div><label for="keterangan" class="block text-sm font-medium text-gray-600">Keterangan</label><textarea name="keterangan" id="keterangan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">{{ old('keterangan', $asset->keterangan) }}</textarea></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection