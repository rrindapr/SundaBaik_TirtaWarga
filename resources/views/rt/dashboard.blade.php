@extends('layouts.app')

@section('title', 'Ringkasan Ketua RT')

@section('content')
    @php
        $statusClasses = [1 => 'menunggu', 2 => 'diverifikasi', 3 => 'selesai'];
        $statusLabels  = [1 => 'Menunggu', 2 => 'Diverifikasi', 3 => 'Selesai'];
    @endphp

    <div class="page-head">
        <div>
            <h1 class="page-title">Halo, {{ auth()->user()->name }}</h1>
            <p class="page-sub">Kirim laporan paceklik air dan pantau tindak lanjutnya dari BPBD.</p>
        </div>
        <a href="#" class="btn-primary">@include('partials.icon', ['name' => 'plus']) Kirim Laporan</a>
    </div>

    <nav class="tabs">
        <a href="#" class="active">Ringkasan</a>
        <a href="#">Riwayat Laporan</a>
        <a href="#">Notifikasi</a>
    </nav>

    <div class="kpi-row">
        @foreach ($stats as $stat)
            <div class="kpi">
                <div class="kpi-label">{{ $stat['label'] }}</div>
                <div class="kpi-value mono">{{ $stat['value'] }}</div>
                <div class="kpi-note">{{ $stat['note'] }}</div>
            </div>
        @endforeach
    </div>

    <section class="section">
        <div class="section-head">
            <h2 class="section-title">Laporan Terakhir</h2>
            <span class="pill {{ $statusClasses[$latestReport['step']] }}">{{ $statusLabels[$latestReport['step']] }}</span>
        </div>
        <div class="card tracker">
            <div class="tracker-head">
                <div>
                    <h3 class="mono">{{ $latestReport['code'] }}</h3>
                    <p>{{ $latestReport['address'] }} · {{ $latestReport['affected'] }} KK terdampak</p>
                </div>
                <p>Dikirim {{ $latestReport['sentAt'] }}</p>
            </div>
            <div class="steps">
                @foreach ($latestReport['timeline'] as $index => $item)
                    @php
                        $stepNumber = $index + 1;
                        $stepClass = $stepNumber < $latestReport['step']
                            ? 'done'
                            : ($stepNumber === $latestReport['step'] ? 'done current' : '');
                    @endphp
                    <div class="step {{ $stepClass }}">
                        <span class="node">@if ($stepNumber <= $latestReport['step'])<svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>@endif</span>
                        <strong>{{ $item['title'] }}</strong>
                        <small>{{ $item['time'] }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="split">
        <div>
            <div class="selects">
                <span class="select">Urutkan: <strong>Terbaru</strong> @include('partials.icon', ['name' => 'chevron'])</span>
                <span class="select">Status: <strong>Semua</strong> @include('partials.icon', ['name' => 'chevron'])</span>
            </div>
            <div class="card table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>↓ ID Laporan</th>
                            <th>↓ Tanggal</th>
                            <th>↓ Alamat</th>
                            <th>↓ Terdampak</th>
                            <th>↓ Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr>
                                <td class="mono"><strong>{{ $report['code'] }}</strong></td>
                                <td>{{ $report['date'] }}</td>
                                <td>{{ $report['address'] }}</td>
                                <td class="mono">{{ $report['affected'] }} KK</td>
                                <td><span class="pill {{ strtolower($report['status']) }}">{{ $report['status'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="align-self:start;margin-top:52px">
            <div class="card-head">
                <h3>Notifikasi</h3>
                <a href="#">Lihat semua</a>
            </div>
            <div class="notif-list">
                @foreach ($notifications as $notification)
                    <div class="notif {{ $notification['isUnread'] ? 'unread' : '' }}">
                        <span class="dot"></span>
                        <div>
                            <strong>{{ $notification['title'] }}</strong>
                            <p>{{ $notification['message'] }}</p>
                            <small>{{ $notification['time'] }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection