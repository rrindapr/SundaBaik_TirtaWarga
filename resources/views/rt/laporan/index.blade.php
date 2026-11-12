@extends('layouts.app')

@section('title', 'Riwayat Laporan')

@section('content')
    @php
        // Kolom status di database berupa angka (lihat migration laporan_paceklik).
        $statusLabels  = [1 => 'Menunggu', 2 => 'Diverifikasi', 3 => 'Selesai'];
        $statusClasses = [1 => 'menunggu', 2 => 'diverifikasi', 3 => 'selesai'];
    @endphp

    <div class="page-head">
        <div>
            <h1 class="page-title">Riwayat Laporan</h1>
            <p class="page-sub">Semua laporan paceklik air yang pernah Anda kirim beserta statusnya.</p>
        </div>
        <a href="#" class="btn-primary">@include('partials.icon', ['name' => 'plus']) Kirim Laporan</a>
    </div>

    @if ($reports->isEmpty())
        <div class="empty">
            <div class="ico">@include('partials.icon', ['name' => 'list'])</div>
            <h2>Belum ada laporan</h2>
            <p>Laporan paceklik air yang Anda kirim akan muncul di sini, lengkap dengan status penanganannya dari BPBD.</p>
        </div>
    @else
        <div class="card table-wrap" style="margin-top: 30px">
            <table class="data">
                <thead>
                    <tr>
                        <th>ID Laporan</th>
                        <th>Tanggal Lapor</th>
                        <th>Alamat</th>
                        <th>Terdampak</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                        <tr>
                            <td class="mono"><a href="{{ route('rt.laporan.show', $report->kode) }}" class="link-strong">{{ $report->kode }}</a></td>
                            <td class="mono">{{ $report->created_at->format('d/m/Y H.i') }}</td>
                            <td>{{ $report->alamat }}</td>
                            <td class="mono">{{ $report->jumlah_kk_terdampak }} KK</td>
                            <td>
                                <span class="pill {{ $statusClasses[$report->status] ?? '' }}">
                                    {{ $statusLabels[$report->status] ?? 'Tidak diketahui' }}
                                </span>
                            </td>
                            <td><a href="{{ route('rt.laporan.show', $report->kode) }}" class="link">Lihat detail ›</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pager">
            <span>Menampilkan {{ $reports->firstItem() }}–{{ $reports->lastItem() }} dari {{ $reports->total() }} laporan</span>
            @if ($reports->hasPages())
                <div class="pager-links">
                    @if ($reports->onFirstPage())
                        <span class="disabled">‹ Sebelumnya</span>
                    @else
                        <a href="{{ $reports->previousPageUrl() }}">‹ Sebelumnya</a>
                    @endif

                    <span class="current">Halaman {{ $reports->currentPage() }} dari {{ $reports->lastPage() }}</span>

                    @if ($reports->hasMorePages())
                        <a href="{{ $reports->nextPageUrl() }}">Berikutnya ›</a>
                    @else
                        <span class="disabled">Berikutnya ›</span>
                    @endif
                </div>
            @endif
        </div>
    @endif
@endsection