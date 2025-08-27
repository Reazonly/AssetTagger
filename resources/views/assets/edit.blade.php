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
             x-data="assetEditForm()">
            
            {{-- Informasi Utama (Tidak Bisa Diedit) --}}
            <div class="bg-white p-8 rounded-lg shadow-md border">
                <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Utama</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label class="block text-sm font-medium text-gray-500">Nama Barang</label><p class="mt-1 text-gray-900 font-semibold">{{ $asset->nama_barang }}</p></div>
                    <div><label class="block text-sm font-medium text-gray-500">Kategori</label><p class="mt-1 text-gray-900 font-semibold">{{ optional($asset->category)->name }}</p></div>
                    @if($asset->subCategory)
                    <div><label class="block text-sm font-medium text-gray-500">Sub Kategori</label><p class="mt-1 text-gray-900 font-semibold">{{ optional($asset->subCategory)->name }}</p></div>
                    @endif
                    @if($asset->merk)
                    <div><label class="block text-sm font-medium text-gray-500">Merk</label><p class="mt-1 text-gray-900 font-semibold">{{ $asset->merk }}</p></div>
                    @endif
                    @if($asset->tipe)
                    <div><label class="block text-sm font-medium text-gray-500">Tipe</label><p class="mt-1 text-gray-900 font-semibold">{{ $asset->tipe }}</p></div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Perusahaan Pemilik</label>
                        <p class="mt-1 text-gray-900 font-semibold">{{ optional($asset->company)->name }}</p>
                        <input type="hidden" name="company_id" value="{{ $asset->company_id }}">
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div class="bg-white p-8 rounded-lg shadow-md border">
                     <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Pengguna</h3>
                    <div>
                        <label for="asset_user_id" class="block text-sm font-medium text-gray-700">Pilih Pengguna (Jika Ada)</label>
                        <select id="asset_user_id" name="asset_user_id" x-model.number="selectedAssetUserId" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm">
                            <option value="">-- Tidak ada pengguna --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('asset_user_id', $asset->asset_user_id) == $user->id ? 'selected' : '' }}>{{ $user->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="selectedAssetUserId" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-md border" x-cloak>
                        <div><label class="block text-sm font-medium text-gray-500">Jabatan</label><p class="mt-1 text-sm text-gray-900" x-text="currentAssetUser.jabatan || '-'"></p></div>
                        <div><label class="block text-sm font-medium text-gray-500">Departemen</label><p class="mt-1 text-sm text-gray-900" x-text="currentAssetUser.departemen || '-'"></p></div>
                        <div><label class="block text-sm font-medium text-gray-500">Perusahaan</label><p class="mt-1 text-sm text-gray-900" x-text="currentAssetUser.company ? currentAssetUser.company.name : '-'"></p></div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md border">
                    <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Detail & Spesifikasi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="serial_number" class="block text-sm font-medium text-gray-600">Serial Number</label><input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm"></div>
                        <div><label for="kondisi" class="block text-sm font-medium text-gray-600">Kondisi</label><select name="kondisi" id="kondisi" required class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm"><option value="Baik" {{ old('kondisi', $asset->kondisi) == 'Baik' ? 'selected' : '' }}>Baik</option><option value="Rusak" {{ old('kondisi', $asset->kondisi) == 'Rusak' ? 'selected' : '' }}>Rusak</option><option value="Perbaikan" {{ old('kondisi', $asset->kondisi) == 'Perbaikan' ? 'selected' : '' }}>Perbaikan</option></select></div>
                        <div><label for="jumlah" class="block text-sm font-medium text-gray-600">Jumlah</label><input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah', $asset->jumlah) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm"></div>
                        <div><label for="satuan" class="block text-sm font-medium text-gray-700">Satuan</label><input type="text" name="satuan" id="satuan" value="{{ old('satuan', $asset->satuan) }}" required class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm"></div>
                        <div class="md:col-span-2"><label for="lokasi" class="block text-sm font-medium text-gray-600">Lokasi Fisik</label><input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi', $asset->lokasi) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm"></div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t" x-show="allSpecFields.length > 0" x-cloak>
                        <h4 class="text-lg font-medium text-gray-800 mb-4">Spesifikasi Detail</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <template x-for="field in allSpecFields" :key="field.key">
                                <div x-show="field.name && field.name.trim() !== ''">
                                    <label class="block text-sm font-medium text-gray-600 capitalize" x-text="field.name"></label>
                                    
                                    <template x-if="field.type === 'textarea'">
                                        <textarea :name="'spec[' + field.key + ']'"
                                                  x-model="specValues[field.key]"
                                                  rows="3"
                                                  class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm"></textarea>
                                    </template>
                                    
                                    <template x-if="field.type !== 'textarea'">
                                        <input :type="field.type || 'text'" 
                                               :name="'spec[' + field.key + ']'"
                                               x-model="specValues[field.key]"
                                               class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm">
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    {{-- POSISI BARU UNTUK KETERANGAN/DESKRIPSI --}}
                    <div class="mt-6 pt-6 border-t">
                        <label for="keterangan" class="block text-sm font-medium text-gray-600">Keterangan / Deskripsi</label>
                        <textarea name="keterangan" id="keterangan" rows="3" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm">{{ old('keterangan', $asset->keterangan ?? ($asset->specifications['Deskripsi'] ?? ($asset->specifications['Keterangan'] ?? ''))) }}</textarea>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md border">
                    <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Pembelian & Dokumen</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tanggal_pembelian" class="block text-sm font-medium text-gray-700">Tanggal Pembelian</label>
                            <input type="date" name="tanggal_pembelian" id="tanggal_pembelian" 
                                   value="{{ old('tanggal_pembelian', optional($asset->tanggal_pembelian)->format('Y-m-d')) }}" 
                                   class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm">
                        </div>
                        <div x-data="{ rawValue: '{{ old('harga_total', $asset->harga_total) }}' }">
                            <label for="harga_total_display" class="block text-sm font-medium text-gray-700">Harga Total (Rp)</label>
                            <input
                                id="harga_total_display" type="text"
                                class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm"
                                x-on:input="rawValue = $event.target.value.replace(/[^0-9]/g, '')"
                                x-bind:value="rawValue === '' || rawValue === null ? '' : 'Rp. ' + parseInt(rawValue, 10).toLocaleString('id-ID')"
                                placeholder="Rp. 0">
                            <input type="hidden" name="harga_total" x-bind:value="rawValue">
                        </div>
                        <div><label for="po_number" class="block text-sm font-medium text-gray-700">Nomor PO</label><input type="text" name="po_number" id="po_number" value="{{ old('po_number', $asset->po_number) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm"></div>
                        <div><label for="nomor" class="block text-sm font-medium text-gray-700">Nomor BAST</label><input type="text" name="nomor" id="nomor" value="{{ old('nomor', $asset->nomor) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm"></div>
                        <div><label for="code_aktiva" class="block text-sm font-medium text-gray-700">Kode Aktiva</label><input type="text" name="code_aktiva" id="code_aktiva" value="{{ old('code_aktiva', $asset->code_aktiva) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm"></div>
                        <div><label for="sumber_dana" class="block text-sm font-medium text-gray-700">Sumber Dana</label><input type="text" name="sumber_dana" id="sumber_dana" value="{{ old('sumber_dana', $asset->sumber_dana) }}" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm"></div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md border">
                    <h3 class="text-xl font-semibold border-b-2 border-black pb-3 mb-6 text-gray-700">Informasi Tambahan</h3>
                    <div class="space-y-6">
                        <div><label for="include_items" class="block text-sm font-medium text-gray-600">Item Termasuk</label><textarea name="include_items" id="include_items" rows="3" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm">{{ old('include_items', $asset->include_items) }}</textarea></div>
                        <div><label for="peruntukan" class="block text-sm font-medium text-gray-600">Peruntukan</label><textarea name="peruntukan" id="peruntukan" rows="3" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm py-2 px-3 text-sm">{{ old('peruntukan', $asset->peruntukan) }}</textarea></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t flex justify-end items-center gap-3">
            <a href="{{ route('assets.show', $asset->id) }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors py-2 px-4 rounded-lg bg-gray-200 hover:bg-gray-300">Batal</a>
            <button type="submit" class="bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700">Simpan Perubahan</button>
        </div>
    </form>

<script>
function assetEditForm() {
    return {
        // Data
        categoriesData: @json($categories->keyBy('id')),
        assetUsersData: @json($users->keyBy('id')),
        selectedAssetUserId: {{ old('asset_user_id', $asset->asset_user_id) ?? 'null' }},
        specValues: @json(old('specifications', $asset->specifications)) ?? {},
        allSpecFields: [],

        get currentAssetUser() { return this.assetUsersData[this.selectedAssetUserId] || {} },
        
        toStudlyCase(str) {
            if (!str) return '';
            return str.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join('');
        },

        init() {
            const categoryId = '{{ $asset->category_id }}';
            const subCategoryId = '{{ $asset->sub_category_id }}';
            const category = this.categoriesData[categoryId];
            const subCategories = category ? category.sub_categories || [] : [];
            const subCategory = subCategories.find(sc => sc.id == subCategoryId);

            let finalFields = new Map();
            let definedFields = (subCategory && subCategory.fields) ? subCategory.fields.filter(f => f.name && f.name.trim() !== '') : [];

            // 1. Tambahkan field yang terdefinisi di sub-kategori
            definedFields.forEach(field => {
                const key = this.toStudlyCase(field.name);
                finalFields.set(key, {
                    key: key,
                    name: field.name,
                    type: field.type || 'text'
                });
            });

            // 2. Tambahkan field dari data yang sudah tersimpan (hasil impor)
            for (const key in this.specValues) {
                if (key.toLowerCase() === 'keterangan' || key.toLowerCase() === 'deskripsi') {
                    continue;
                }

                if (!finalFields.has(key)) {
                    const name = key.replace(/([A-Z])/g, ' $1').trim();
                    const value = this.specValues[key];
                    let type = 'text';
                    if (!isNaN(parseFloat(value)) && isFinite(value) && String(value).trim() !== '') {
                        type = 'number';
                    }
                    
                    finalFields.set(key, { key: key, name: name, type: type });
                }
            }

            this.allSpecFields = Array.from(finalFields.values());
        }
    }
}
</script>
@endsection