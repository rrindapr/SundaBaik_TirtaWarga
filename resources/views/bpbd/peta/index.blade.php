@extends('layouts.app')

@section('title', 'Peta Krisis')

@section('content')
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    <link rel="stylesheet" href="{{ asset('css/peta-krisis.css') }}">

    <div class="page-head">
        <div>
            <h1 class="page-title">Peta Krisis</h1>
            <p class="page-sub">Sebaran titik lokasi laporan paceklik air di wilayah Bandung.</p>
        </div>
        <span class="count-badge">{{ count($titikLaporan) }} dari {{ $totalLaporan }} laporan di peta</span>
    </div>

    @if ($tanpaKoordinat > 0)
        <div class="alert" style="margin-top: 20px; background: var(--st-wait-bg); color: var(--st-wait-ink);">
            <span>{{ $tanpaKoordinat }} laporan belum memiliki koordinat sehingga belum tampil di peta. Datanya tetap ada di menu Laporan Masuk.</span>
        </div>
    @endif

    <div class="card peta-card">
        <div id="petaKrisis" aria-label="Peta sebaran laporan krisis air Bandung"></div>

        <div id="petaFallback" class="peta-fallback" hidden>
            <strong>Peta tidak dapat dimuat.</strong>
            <span>Periksa koneksi internet Anda, lalu muat ulang halaman. Data laporan tetap dapat dilihat di menu Laporan Masuk.</span>
        </div>
    </div>

    {{-- PBI-17: legenda warna titik --}}
    <div class="peta-legend">
        <span><i class="dot kritis"></i> Kritis</span>
        <span><i class="dot sedang"></i> Sedang</span>
        <span><i class="dot rendah"></i> Rendah</span>
        <span><i class="dot ring"></i> Sudah ditetapkan penerima bantuan</span>
    </div>

    <script type="application/json" id="petaConfig">@json($petaConfig)</script>
    <script type="application/json" id="petaTitik">@json($titikLaporan)</script>
    <script>window.PETA_URL_LAPORAN = @json($urlLaporan);</script>
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('js/peta-krisis.js') }}"></script>
@endsection