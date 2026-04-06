<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('tajuk', 'Sistem Zakat Kedah')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    {{-- Bar Navigasi --}}
    <nav class="bg-emerald-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo & Nama Sistem --}}
                <div class="flex items-center space-x-3">
                    <div class="bg-white rounded-lg p-1.5">
                        <svg class="w-7 h-7 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <a href="{{ route('pembayar.index') }}" class="text-white font-bold text-lg tracking-wide">
                        Sistem Zakat Kedah
                    </a>
                </div>

                {{-- Pautan Navigasi --}}
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('pembayar.index') }}"
                       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200
                              {{ request()->routeIs('pembayar.*') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }}">
                        Pembayar
                    </a>
                    <a href="{{ route('semak') }}"
                       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200
                              {{ request()->routeIs('semak') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }}">
                        Semak Sistem
                    </a>
                </div>

                {{-- Menu Mudah Alih --}}
                <div class="md:hidden">
                    <button onclick="document.getElementById('menu-mudah-alih').classList.toggle('hidden')"
                            class="text-emerald-100 hover:text-white p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Menu Mudah Alih (tersembunyi) --}}
        <div id="menu-mudah-alih" class="hidden md:hidden bg-emerald-800 pb-3 px-4">
            <a href="{{ route('pembayar.index') }}" class="block px-3 py-2 rounded-lg text-sm text-emerald-100 hover:bg-emerald-600 hover:text-white">
                Pembayar
            </a>
            <a href="{{ route('semak') }}" class="block px-3 py-2 rounded-lg text-sm text-emerald-100 hover:bg-emerald-600 hover:text-white">
                Semak Sistem
            </a>
        </div>
    </nav>

    {{-- Kandungan Utama --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Mesej Flash --}}
        @if (session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start space-x-3">
                <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-emerald-800 text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start space-x-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-800 text-sm font-medium">{{ session('error') }}</p>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Kaki Halaman --}}
    <footer class="bg-emerald-800 text-emerald-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row items-center justify-between space-y-2 md:space-y-0">
                <p class="text-sm">&copy; 2024 Pusat Zakat Negeri Kedah. Hak cipta terpelihara.</p>
                <p class="text-xs text-emerald-400">Sistem Pengurusan Zakat — Kursus Laravel Hari 1</p>
            </div>
        </div>
    </footer>

</body>
</html>
