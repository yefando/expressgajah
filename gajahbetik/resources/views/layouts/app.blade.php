@php
use Illuminate\Support\Str;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Gajah Betik') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    
</head>
<body class="bg-light"> {{-- Ubah warna latar belakang body menjadi lebih cerah --}}

    <div class="d-flex flex-column min-vh-100"> {{-- Menggunakan flexbox untuk layout vertikal --}}
        
        {{-- Navbar --}}
        @include('layouts.navigation')

        {{-- Header Halaman --}}
        @isset($header)
            <header class="bg-white shadow-sm py-3 mb-4"> {{-- Padding & margin bawah lebih konsisten --}}
                <div class="container"> {{-- Menggunakan container untuk lebar yang konsisten --}}
                    <h2 class="h5 mb-0 text-dark"> {{-- Ukuran heading dan warna --}}
                        {{ $header }}
                    </h2>
                </div>
            </header>
        @endisset

        {{-- Konten Utama --}}
        <main class="flex-grow-1"> {{-- Konten utama mengisi ruang yang tersedia --}}
            <div class="container"> {{-- Konten berada dalam container Bootstrap --}}
                {{ $slot }}
            </div>
        </main>

        {{-- Footer (Opsional, tapi bagus untuk konsistensi) --}}
        <footer class="bg-dark text-white text-center py-3 mt-4"> {{-- Footer gelap, teks putih, padding --}}
            <div class="container">
                <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'Gajah Betik') }}. All rights reserved.</p>
            </div>
        </footer>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script src="{{ asset('js/custom.js') }}"></script>

    @stack('scripts') 

</body>
</html>