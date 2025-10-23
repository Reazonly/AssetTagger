@extends('layouts.app')
@section('title', 'Laporan Alokasi Aset Saat Ini')

@section('content')

<div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
    
    {{-- HEADER DAN TOMBOL AKSI --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Laporan Alokasi Aset Saat Ini</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar aset yang sedang dialokasikan kepada pengguna.</p>
        </div>
        <div class="flex items-center gap-3 mt-4 md:mt-0">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center gap-2 bg-sky-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-sky-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 h-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Aksi Laporan
                </button>
                <div x-show="open" @click.outside="open = false" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu">
                    <div class="py-1" role="none">
                        {{-- TOMBOL EXPORT PDF BARU --}}
                        <a href="{{ route('reports.tracking.pdf') }}?{{ request()->getQueryString() }}" target="_blank" class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                            Cetak (PDF)
                        </a>
                        {{-- TOMBOL EXPORT EXCEL BARU --}}
                        <a href="{{ route('reports.tracking.excel') }}?{{ request()->getQueryString() }}" class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                            Ekspor Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- KOTAK FILTER DAN PENCARIAN --}}
    <form method="GET" action="{{ route('reports.tracking') }}" class="mb-6"
          x-data="{ 
              selectedCategory: '{{ $selectedFilters['category_id'] ?? '' }}',
              allSubCategories: {{ 
                  $subCategories->pluck('name', 'id')->mapWithKeys(fn($name, $id) => [
                      $id => ['name' => $name, 'category_id' => $subCategories->firstWhere('id', $id)->category_id]
                  ])->toJson()
              }},
              filteredSubCategories: {},
              
              filterSubCategories() {
                  this.filteredSubCategories = Object.entries(this.allSubCategories)
                      .filter(([id, sub]) => !this.selectedCategory || sub.category_id == this.selectedCategory)
                      .reduce((obj, [id, sub]) => {
                          obj[id] = sub.name;
                          return obj;
                      }, {});
                  
                  const subCategoryIdInput = document.getElementById('sub_category_id');
                  if (subCategoryIdInput && subCategoryIdInput.value && !this.filteredSubCategories[subCategoryIdInput.value]) {
                      subCategoryIdInput.value = '';
                  }
              }
          }"
          x-init="filterSubCategories()"
    >
        <div class="flex flex-wrap items-center gap-3">
            
            {{-- Filter Kategori --}}
            <div>
                <label for="category_id" class="sr-only">Kategori</label>
                <select id="category_id" name="category_id" x-model="selectedCategory" @change="filterSubCategories"
                        class="w-full md:w-64 border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    <option value="">Filter Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(isset($selectedFilters['category_id']) && $selectedFilters['category_id'] == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Sub-Kategori (DINAMIS) --}}
            <div>
                <label for="sub_category_id" class="sr-only">Sub-Kategori</label>
                <select id="sub_category_id" name="sub_category_id"
                        class="w-full md:w-64 border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    <option value="">Filter Sub-Kategori</option>
                    <template x-for="(name, id) in filteredSubCategories" :key="id">
                        <option :value="id" 
                                :selected="id == '{{ $selectedFilters['sub_category_id'] ?? '' }}'" 
                                x-text="name"></option>
                    </template>
                </select>
            </div>

            {{-- Filter Pengguna Saat Ini --}}
            <div>
                <label for="asset_user_id" class="sr-only">Pengguna Saat Ini</label>
                <select id="asset_user_id" name="asset_user_id" 
                        class="w-full md:w-64 border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    <option value="">Filter Pengguna</option>
                    @foreach($assetUsers as $user)
                        <option value="{{ $user->id }}" @selected(isset($selectedFilters['asset_user_id']) && $selectedFilters['asset_user_id'] == $user->id)>
                            {{ $user->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- Filter Perusahaan Aset --}}
            <div>
                <label for="company_id" class="sr-only">Perusahaan Aset</label>
                <select id="company_id" name="company_id" 
                        class="w-full md:w-64 border-2 border-gray-300 rounded-lg shadow-sm py-2 px-3 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    <option value="">Filter Perusahaan</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected(isset($selectedFilters['company_id']) && $selectedFilters['company_id'] == $company->id)>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol Filter dan Reset (Disesuaikan ukurannya) --}}
            <button type="submit" class="h-10 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" /></svg>
                Filter
            </button>
            <a href="{{ route('reports.tracking') }}" class="h-10 inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                Reset
            </a>
        </div>
    </form>
    
    {{-- TABEL LAPORAN (Sama seperti sebelumnya) --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-600">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">No.</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">Kode & Nama Aset</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">Kategori</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">Pengguna Saat Ini</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">Jabatan & Perusahaan</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Kondisi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($currentAllocations as $index => $asset)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="font-semibold text-gray-900">{{ $asset->code_asset }}</p>
                            <p class="text-xs text-gray-500">{{ $asset->nama_barang }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->category->name ?? 'N/A' }}</td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($asset->assetUser)
                                <p class="font-medium text-gray-900">{{ $asset->assetUser->nama }}</p>
                            @else
                                <span class="text-red-500 font-semibold">Tidak Dialokasikan</span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4">
                             @if($asset->assetUser)
                                <p class="text-xs text-gray-700">{{ $asset->assetUser->jabatan ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500 font-medium">{{ $asset->assetUser->company->name ?? 'N/A' }}</p>
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                  @if ($asset->kondisi == 'Baik')
                                      bg-green-100 text-green-800
                                  @elseif ($asset->kondisi == 'Rusak')
                                      bg-red-100 text-red-800
                                  @else
                                      bg-yellow-100 text-yellow-800
                                  @endif
                                  ">
                                {{ $asset->kondisi }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-lg font-medium text-gray-500">
                            Tidak ada data aset yang sedang dialokasikan saat ini, atau tidak ada yang cocok dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection