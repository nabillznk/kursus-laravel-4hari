<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Sistem Zakat Kedah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    {{-- Navbar --}}
    <nav class="bg-emerald-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                {{-- Left: Brand + Links --}}
                <div class="flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <svg class="w-7 h-7 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-white font-bold text-lg">Sistem Zakat Kedah</span>
                    </a>

                    <div class="hidden md:flex items-center space-x-1">
                        <a href="{{ route('dashboard') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }} transition">
                            Dashboard
                        </a>
                        <a href="{{ route('pembayar.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('pembayar.*') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }} transition">
                            Pembayar
                        </a>
                        <a href="{{ route('jenis-zakat.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('jenis-zakat.*') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }} transition">
                            Jenis Zakat
                        </a>
                        <a href="{{ route('pembayaran.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('pembayaran.*') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-600 hover:text-white' }} transition">
                            Pembayaran
                        </a>
                    </div>
                </div>

                {{-- Right: User --}}
                <div class="flex items-center space-x-4">
                    <span class="text-emerald-100 text-sm hidden sm:block">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="bg-emerald-800 hover:bg-emerald-900 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                            Log Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div class="md:hidden border-t border-emerald-600 px-4 py-2 space-y-1">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-emerald-100 hover:bg-emerald-600">Dashboard</a>
            <a href="{{ route('pembayar.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-emerald-100 hover:bg-emerald-600">Pembayar</a>
            <a href="{{ route('jenis-zakat.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-emerald-100 hover:bg-emerald-600">Jenis Zakat</a>
            <a href="{{ route('pembayaran.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-emerald-100 hover:bg-emerald-600">Pembayaran</a>
        </div>
    </nav>

    {{-- Flash Messages --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if(session('success'))
            <x-alert type="success">{{ session('success') }}</x-alert>
        @endif
        @if(session('error'))
            <x-alert type="error">{{ session('error') }}</x-alert>
        @endif
    </div>

    {{-- Main Content --}}
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} Pusat Zakat Negeri Kedah. Sistem Pengurusan Zakat.
            </p>
        </div>
    </footer>

</body>
</html>
