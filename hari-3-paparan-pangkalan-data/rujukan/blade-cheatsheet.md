# Rujukan Pantas Blade — Sistem Zakat

## Memapar Data

| Sintaks | Tujuan | Contoh |
|---------|--------|--------|
| `{{ $var }}` | Papar data (HTML escaped) | `{{ $pembayar->nama }}` |
| `{!! $var !!}` | Papar HTML mentah (tidak escaped) | `{!! $penerangan !!}` |
| `{{ $var ?? 'lalai' }}` | Papar dengan nilai lalai | `{{ $pembayar->email ?? 'Tiada' }}` |

## Arahan Kawalan

```blade
{{-- Bersyarat --}}
@if($pembayars->count() > 0)
    <p>{{ $pembayars->count() }} pembayar berdaftar.</p>
@else
    <p>Tiada pembayar dijumpai.</p>
@endif

{{-- Gelung senarai pembayar --}}
@foreach($pembayars as $pembayar)
    <tr>
        <td>{{ $pembayar->nama }}</td>
        <td>{{ $pembayar->no_ic }}</td>
        <td>{{ $pembayar->no_tel }}</td>
    </tr>
@endforeach

{{-- Gelung dengan keadaan kosong --}}
@forelse($pembayarans as $bayar)
    <p>Resit: {{ $bayar->no_resit }} — RM {{ $bayar->jumlah }}</p>
@empty
    <p>Tiada rekod pembayaran dijumpai.</p>
@endforelse

{{-- Semak pengesahan kakitangan --}}
@auth
    <p>Selamat datang, {{ auth()->user()->name }}!</p>
@endauth

@guest
    <p>Sila log masuk untuk mengakses sistem.</p>
@endguest
```

## Layout & Warisan

```blade
{{-- Layout induk: layouts/app.blade.php --}}
<html>
<body>
    <nav>Pusat Zakat Negeri Kedah</nav>
    @yield('content')
</body>
</html>

{{-- Halaman anak: pembayar/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Senarai Pembayar')

@section('content')
    <h1>Senarai Pembayar Zakat</h1>
    {{-- kandungan --}}
@endsection
```

## Borang Pendaftaran Pembayar

```blade
<form action="{{ route('pembayar.store') }}" method="POST">
    @csrf  {{-- Token CSRF — WAJIB untuk semua borang POST --}}

    <input type="text" name="nama" value="{{ old('nama') }}">
    @error('nama')
        <span class="error">{{ $message }}</span>
    @enderror

    <input type="text" name="no_ic" value="{{ old('no_ic') }}"
           placeholder="Contoh: 850101145678">
    @error('no_ic')
        <span class="error">{{ $message }}</span>
    @enderror

    <button type="submit">Daftar Pembayar</button>
</form>

{{-- Borang kemaskini (PUT) --}}
<form action="{{ route('pembayar.update', $pembayar) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="text" name="nama" value="{{ old('nama', $pembayar->nama) }}">
    <button type="submit">Kemaskini</button>
</form>

{{-- Borang padam (DELETE) --}}
<form action="{{ route('pembayar.destroy', $pembayar) }}" method="POST">
    @csrf
    @method('DELETE')
    <button type="submit" onclick="return confirm('Pasti padam?')">Padam</button>
</form>
```

## Sertakan Sub-Paparan

```blade
@include('partials.navbar')
@include('partials.footer')
@include('pembayar._form', ['pembayar' => $pembayar])
```

## Arahan Blade Lain

```blade
{{-- Papar ralat pengesahan --}}
@if($errors->any())
    <ul>
        @foreach($errors->all() as $ralat)
            <li>{{ $ralat }}</li>
        @endforeach
    </ul>
@endif

{{-- Asset URL --}}
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<img src="{{ asset('images/logo-zakat.png') }}">

{{-- URL bernama --}}
<a href="{{ route('pembayar.show', $pembayar) }}">Lihat</a>
<a href="{{ route('pembayar.edit', $pembayar) }}">Edit</a>

{{-- Gelung dengan $loop --}}
@foreach($pembayars as $p)
    {{ $loop->iteration }}. {{ $p->nama }}
    @if($loop->last) — Akhir senarai @endif
@endforeach
```
