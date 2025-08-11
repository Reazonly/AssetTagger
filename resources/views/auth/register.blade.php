<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - AssetTagger</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .btn-jhonlin { background-color: #0052a5; }
        .btn-jhonlin:hover { background-color: #004182; }
    </style>
</head>
<body class="bg-gray-100">

    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md bg-white rounded-xl shadow-2xl p-8 m-4">
            
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/jhonlin_logo.png') }}" alt="Logo Jhonlin Group" class="h-16">
            </div>

            <h2 class="text-2xl font-bold text-center text-gray-800 mb-1">Buat Akun Baru</h2>
            <p class="text-center text-gray-500 mb-8">Daftar untuk mulai mengelola aset.</p>

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label for="nama_pengguna" class="text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="nama_pengguna" id="nama_pengguna" value="{{ old('nama_pengguna') }}" required class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="email" class="text-sm font-medium text-gray-700">Alamat Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="password" class="text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="password" required class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="password_confirmation" class="text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-4 text-red-500 text-sm">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <button type="submit" class="w-full mt-8 py-3 px-4 btn-jhonlin text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                        Daftar
                    </button>
                </div>

            </form>
        </div>
    </div>

</body>
</html>