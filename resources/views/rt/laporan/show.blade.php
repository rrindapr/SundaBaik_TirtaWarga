@extends('layouts.app')

@section('title', 'Detail Laporan ' . $report->kode)

@section('content')
    @php
        // Kolom status di database berupa angka (lihat migration laporan_paceklik).
        $statusLabels  = [1 => 'Menunggu', 2 => 'Diverifikasi', 3 => 'Selesai'];
        $statusClasses = [1 => 'menunggu', 2 => 'diverifikasi', 3 => 'selesai'];

        // Tahapan penanganan laporan, diambil dari kolom waktu di tabel.
        $timeline = [
            ['title' => 'Laporan dikirim',   'time' => $report->created_at],
            ['title' => 'Diverifikasi BPBD', 'time' => $report->diverifikasi_at],
            ['title' => 'Bantuan selesai',   'time' => $report->selesai_at],
        ];
    @endphp

    <a href="{{ route('rt.laporan.index') }}" class="back-link">‹ Kembali ke Riwayat Laporan</a>

    <div class="page-head">
        <div>
            <h1 class="page-title">{{ $report->kode }}</h1>
            <p class="page-sub">Dikirim {{ $report->created_at->format('d/m/Y H.i') }}</p>
        </div>
        <span class="pill {{ $statusClasses[$report->status] ?? '' }}">
            {{ $statusLabels[$report->status] ?? 'Tidak diketahui' }}
        </span>
    </div>

    <section class="section">
        <h2 class="section-title">Status Penanganan</h2>
        <div class="card tracker" style="margin-top: 18px">
            <div class="steps">
                @foreach ($timeline as $index => $item)
                    @php
                        $stepNumber = $index + 1;
                        $stepClass = $stepNumber < $report->status
                            ? 'done'
                            : ($stepNumber === (int) $report->status ? 'done current' : '');
                    @endphp
                    <div class="step {{ $stepClass }}">
                        <span class="node">@if ($stepNumber <= $report->status)<svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>@endif</span>
                        <strong>{{ $item['title'] }}</strong>
                        <small>{{ $item['time'] ? $item['time']->format('d/m/Y H.i') : '—' }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section" style="border-bottom: 0">
        <h2 class="section-title">Data Laporan</h2>
        <dl class="detail-grid card">
            <div>
                <dt>ID Laporan</dt>
                <dd class="mono">{{ $report->kode }}</dd>
            </div>
            <div>
                <dt>Tanggal Lapor</dt>
                <dd class="mono">{{ $report->created_at->format('d/m/Y H.i') }}</dd>
            </div>
            <div>
                <dt>Jumlah Terdampak</dt>
                <dd class="mono">{{ $report->jumlah_kk_terdampak }} KK</dd>
            </div>
            <div>
                <dt>Status</dt>
                <dd>{{ $statusLabels[$report->status] ?? 'Tidak diketahui' }}</dd>
            </div>
            <div class="wide">
                <dt>Alamat</dt>
                <dd>{{ $report->alamat }}</dd>
            </div>
            <div class="wide">
                <dt>Keterangan</dt>
                <dd>{{ $report->keterangan ?: 'Tidak ada keterangan.' }}</dd>
            </div>
        </dl>
    </section>
@endsection