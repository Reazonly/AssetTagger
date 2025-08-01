<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Detail Aset') - Jhonlin Group</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="container mx-auto p-4 sm:p-8">
        <main class="bg-white p-6 sm:p-8 rounded-lg shadow-lg max-w-4xl mx-auto">
            {{-- Ini adalah tempat konten halaman akan disisipkan --}}
            @yield('content')
        </main>
        <footer class="text-center text-gray-400 text-xs mt-6 flex items-center justify-center">
            <img src="{{ asset('images/jhonlin_logo.png') }}" alt="Jhonlin Group Logo" class="h-5 mr-2">
            Powered by AssetTagger Jhonlin Group
        </footer>
    </div>
</body>
</html>
