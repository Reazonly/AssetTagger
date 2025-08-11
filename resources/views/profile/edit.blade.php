@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="max-w-2xl mx-auto">
    
   
    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8 flex items-center border">
        <div class="h-20 w-20 rounded-full bg-sky-800 flex-shrink-0 flex items-center justify-center border-4 border-sky-200">
            <span class="text-3xl font-bold text-white uppercase">
                @php
                    $nameParts = explode(' ', Auth::user()->nama_pengguna);
                    $initials = count($nameParts) > 1 ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1)) : strtoupper(substr($nameParts[0], 0, 1));
                @endphp
                {{ $initials }}
            </span>
        </div>
        <div class="ml-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ auth()->user()->nama_pengguna }}</h1>
            <p class="text-sm font-semibold uppercase tracking-wider text-white bg-sky-600 px-3 py-1 rounded-full inline-block mt-2">
                {{ auth()->user()->role }}
            </p>
        </div>
    </div>

   
    <div class="mt-8 bg-white rounded-xl shadow-lg border">
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6 md:p-8">
                <h2 class="text-2xl font-bold text-gray-800 border-b-2 border-black pb-3 mb-6">Ubah Password</h2>
                <p class="text-sm text-gray-500 mt-1">Pastikan Anda menggunakan password yang kuat dan mudah diingat.</p>

                @if ($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 my-6 rounded-md" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="mt-6 space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                        <input type="password" name="current_password" id="current_password" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 py-2 px-3" required>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                        <input type="password" name="password" id="password" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 py-2 px-3" required>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-2 border-gray-400 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 py-2 px-3" required>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end">
                <button type="submit" class="bg-sky-600 text-white font-semibold px-5 py-2 rounded-lg hover:bg-sky-700 shadow-sm transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
