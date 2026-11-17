@extends('layouts.app')

@section('title', 'Peta Krisis')

@section('content')
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    <link rel="stylesheet" href="{{ asset('css/peta-krisis.css') }}">

    <div class="page-head">
        <div>
            <h1 class="page-title">Peta Krisis</h1>
            <p class="page-sub">Peta dasar wilayah Bandung untuk memantau sebaran krisis air bersih.</p>
        </div>
        <span class="count-badge">{{ $totalLaporan }} laporan tercatat</span>
    </div>

    <div class="card peta-card">
        <div id="petaKrisis" aria-label="Peta wilayah krisis air Bandung"></div>

        {{-- Ditampilkan jika Leaflet atau tile peta gagal dimuat (UC-05: peta gagal dimuat) --}}
        <div id="petaFallback" class="peta-fallback" hidden>
            <strong>Peta tidak dapat dimuat.</strong>
            <span>Periksa koneksi internet Anda, lalu muat ulang halaman. Data laporan tetap dapat dilihat di menu Laporan Masuk.</span>
        </div>
    </div>

    <script type="application/json" id="petaConfig">@json($petaConfig)</script>
   <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('js/peta-krisis.js') }}"></script>
@endsection