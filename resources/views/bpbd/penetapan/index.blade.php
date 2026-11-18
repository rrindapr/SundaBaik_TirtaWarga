@extends('layouts.app')

@section('title', 'Penetapan Bantuan')

@section('content')
    {{-- PBI-22 --}}
    <link rel="stylesheet" href="{{ asset('css/bpbd-penetapan.css') }}">

    @php
        // Warna pill status_bantuan (enum di tabel penetapan_bantuan).
        $statusClasses = [
            'Aktif'            => 'ditetapkan',
            'Dalam Pengiriman' => 'diverifikasi',
            'Selesai'          => 'selesai',
            'Dibatalkan'       => 'dibatalkan',
        ];
        $severityClasses = ['Kritis' => 'kritis', 'Sedang' => 'sedang', 'Rendah' => 'rendah'];
        $angka = fn ($nilai) => number_format((int) $nilai, 0, ',', '.');
    @endphp

    <div class="page-head">
        <div>
            <h1 class="page-title">Penetapan Bantuan</h1>
            <p class="page-sub">Daftar wilayah yang sudah ditetapkan BPBD sebagai penerima bantuan air bersih.</p>
        </div>
        <a href="{{ route('bpbd.laporan.index') }}" class="btn-primary">@include('partials.icon', ['name' => 'flag']) Tetapkan wilayah baru</a>
    </div>

    {{-- Ringkasan (penetapan yang dibatalkan tidak dihitung) --}}
    <div class="kpi-row">
        <div class="kpi">
            <div class="kpi-label">Wilayah penerima bantuan</div>
            <div class="kpi-value" data-testid="ringkasan-wilayah">{{ $angka($ringkasan['wilayah']) }}</div>
            <div class="kpi-note">wilayah RT sudah ditetapkan</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Warga terdampak tertangani</div>
            <div class="kpi-value">{{ $angka($ringkasan['kk']) }} KK</div>
            <div class="kpi-note">dari laporan yang ditetapkan</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Total alokasi air</div>
            <div class="kpi-value">{{ $angka($ringkasan['liter']) }} L</div>
            <div class="kpi-note">{{ $angka($ringkasan['liter'] / 5000) }} tangki @ 5.000 liter</div>
        </div>
    </div>

    {{-- Filter status --}}
    <nav class="tabs" aria-label="Filter status penetapan">
        <a href="{{ route('bpbd.penetapan.index', array_filter(['cari' => $cari])) }}"
           class="{{ $statusDipilih === null ? 'active' : '' }}">
            Semua <span class="tab-count">{{ $totalSemua }}</span>
        </a>
        @foreach ($daftarStatus as $status)
            <a href="{{ route('bpbd.penetapan.index', array_filter(['status' => $status, 'cari' => $cari])) }}"
               class="{{ $statusDipilih === $status ? 'active' : '' }}">
                {{ $status }} <span class="tab-count">{{ $jumlahPerStatus[$status] ?? 0 }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Pencarian --}}
    <form method="GET" action="{{ route('bpbd.penetapan.index') }}" class="penetapan-search">
        @if ($statusDipilih)
            <input type="hidden" name="status" value="{{ $statusDipilih }}">
        @endif
        <input type="search" name="cari" value="{{ $cari }}" placeholder="Cari no. surat tugas, ID laporan, atau alamat…" aria-label="Cari penetapan">
        <button type="submit" class="btn-sm">Cari</button>
        @if ($cari !== '')
            <a href="{{ route('bpbd.penetapan.index', array_filter(['status' => $statusDipilih])) }}" class="link">Hapus pencarian</a>
        @endif
    </form>

    @if ($daftarPenetapan->isEmpty())
        <div class="empty">
            <div class="ico">@include('partials.icon', ['name' => 'flag'])</div>
            @if ((int) $totalSemua === 0)
                <h2>Belum ada wilayah yang ditetapkan</h2>
                <p>Tetapkan wilayah penerima bantuan dari menu Laporan Masuk. Wilayah yang sudah ditetapkan akan muncul di sini.</p>
            @else
                <h2>Tidak ada penetapan yang cocok</h2>
                <p>Coba ubah filter status atau kata kunci pencarian.</p>
            @endif
        </div>
    @else
        <div class="card table-wrap" style="margin-top: 24px">
            <table class="data">
                <thead>
                    <tr>
                        <th>No. Surat Tugas</th>
                        <th>ID Laporan</th>
                        <th>Wilayah</th>
                        <th>Terdampak</th>
                        <th>Alokasi &amp; Jadwal</th>
                        <th>Petugas BPBD</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftarPenetapan as $penetapan)
                        @php
                            $laporan = $penetapan->laporan;
                            $jadwal = \Illuminate\Support\Carbon::parse($penetapan->jadwal_distribusi);
                        @endphp
                        <tr id="penetapan-{{ $penetapan->id }}">
                            <td>
                                <div class="cell-stack">
                                    <strong class="mono">{{ $penetapan->nomor_surat_tugas }}</strong>
                                    {{-- tanggal penetapan = waktu record penetapan dibuat --}}
                                    <small class="mono">Ditetapkan {{ $penetapan->created_at->format('d/m/Y H.i') }}</small>
                                </div>
                            </td>
                            <td class="mono">{{ $laporan->kode ?? '—' }}</td>
                            <td class="cell-wrap">
                                {{ $laporan->alamat ?? '—' }}
                                <small class="cell-note">
                                    Pelapor: {{ $laporan?->user?->name ?? 'Tidak diketahui' }}
                                    @if ($laporan?->tingkat_keparahan)
                                        · <span class="sev {{ $severityClasses[$laporan->tingkat_keparahan] ?? 'sedang' }}">{{ $laporan->tingkat_keparahan }}</span>
                                    @endif
                                </small>
                            </td>
                            <td class="mono">{{ $laporan->jumlah_kk_terdampak ?? 0 }} KK</td>
                            <td>
                                <div class="cell-stack">
                                    <span class="mono">{{ $angka($penetapan->alokasi_tangki_liter) }} liter</span>
                                    <small class="mono">Distribusi {{ $jadwal->format('d/m/Y') }}</small>
                                </div>
                            </td>
                            <td>{{ $penetapan->petugas_bpbd }}</td>
                            <td>
                                <span class="pill {{ $statusClasses[$penetapan->status_bantuan] ?? '' }}">
                                    {{ $penetapan->status_bantuan }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pager">
            <span>Menampilkan {{ $daftarPenetapan->firstItem() }}–{{ $daftarPenetapan->lastItem() }} dari {{ $daftarPenetapan->total() }} penetapan</span>
            @if ($daftarPenetapan->hasPages())
                <div class="pager-links">
                    @if ($daftarPenetapan->onFirstPage())
                        <span class="disabled">‹ Sebelumnya</span>
                    @else
                        <a href="{{ $daftarPenetapan->previousPageUrl() }}">‹ Sebelumnya</a>
                    @endif

                    <span class="current">Halaman {{ $daftarPenetapan->currentPage() }} dari {{ $daftarPenetapan->lastPage() }}</span>

                    @if ($daftarPenetapan->hasMorePages())
                        <a href="{{ $daftarPenetapan->nextPageUrl() }}">Berikutnya ›</a>
                    @else
                        <span class="disabled">Berikutnya ›</span>
                    @endif
                </div>
            @endif
        </div>
    @endif
@endsection