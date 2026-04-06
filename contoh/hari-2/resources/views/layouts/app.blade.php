<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Zakat Kedah')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    {{-- Bar navigasi --}}
    <nav class="bg-emerald-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('pembayar.index') }}" class="text-white font-bold text-xl">
                        Sistem Zakat Kedah
                    </a>
                </div>
                <div class="flex items-center space-x-1">
                    <a href="{{ route('pembayar.index') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('pembayar.*') ? 'bg-emerald-900 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }}">
                        Pembayar
                    </a>
                    <a href="{{ route('maklumat.laluan') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('maklumat.*') ? 'bg-emerald-900 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }}">
                        Maklumat Laluan
                    </a>
                    <a href="{{ route('semak') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('semak') ? 'bg-emerald-900 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }}">
                        Semak Sistem
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Mesej kilat --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif
    </div>

    {{-- Kandungan utama --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    {{-- Kaki halaman --}}
    <footer class="bg-emerald-800 text-emerald-100 text-center py-4 mt-8">
        <p class="text-sm">Pusat Zakat Negeri Kedah &copy; 2024</p>
    </footer>

</body>
</html>
