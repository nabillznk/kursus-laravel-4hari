<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran — Sistem Zakat Kedah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-600 to-emerald-800 flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl p-8">
        {{-- Logo / Tajuk --}}
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-emerald-700">Sistem Zakat Kedah</h1>
            <p class="text-gray-500 mt-1">Pendaftaran Akaun Baru</p>
        </div>

        {{-- Borang Pendaftaran --}}
        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Nama Penuh --}}
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Penuh</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                       placeholder="Masukkan nama penuh" required autofocus>
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- E-mel --}}
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mel</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                       placeholder="contoh@email.com" required>
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kata Laluan --}}
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Laluan</label>
                <input type="password" name="password" id="password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                       placeholder="Minimum 8 aksara" required>
                @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sahkan Kata Laluan --}}
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Sahkan Kata Laluan</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                       placeholder="Masukkan semula kata laluan" required>
            </div>

            {{-- Butang Daftar --}}
            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-lg transition duration-200">
                Daftar
            </button>
        </form>

        {{-- Pautan Log Masuk --}}
        <p class="text-center text-sm text-gray-500 mt-6">
            Sudah mempunyai akaun?
            <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Log masuk</a>
        </p>
    </div>

</body>
</html>
