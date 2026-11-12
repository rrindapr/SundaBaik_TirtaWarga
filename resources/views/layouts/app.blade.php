<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · TirtaWarga</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/tirtawarga.css') }}">
</head>
<body>
@php
    $user = auth()->user();
    $isBpbd = $user->role === 'bpbd';

    // 'route': null artinya halaman belum dibuat (link sementara '#').
    $menu = $isBpbd
        ? [
            ['label' => 'Ringkasan',             'route' => 'bpbd.dashboard', 'icon' => 'grid'],
            ['label' => 'Laporan Masuk',         'route' => null,             'icon' => 'inbox', 'badge' => 14],
            ['label' => 'Peta Krisis',           'route' => null,             'icon' => 'map'],
            ['label' => 'Penetapan Bantuan',     'route' => null,             'icon' => 'flag'],
            ['label' => 'Verifikasi Pengiriman', 'route' => null,             'icon' => 'check'],
        ]
        : [
            ['label' => 'Ringkasan',       'route' => 'rt.dashboard',     'icon' => 'grid'],
            ['label' => 'Kirim Laporan',   'route' => null,               'icon' => 'send'],
            ['label' => 'Riwayat Laporan', 'route' => 'rt.laporan.index', 'icon' => 'list'],
            ['label' => 'Notifikasi',      'route' => null,               'icon' => 'bell', 'badge' => 2],
        ];

    $words = preg_split('/\s+/', trim($user->name));
    $initials = strtoupper(substr($words[0] ?? 'T', 0, 1) . substr($words[1] ?? '', 0, 1));
@endphp

<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <a href="{{ url('/') }}" class="brand-logo">@include('partials.logo') TirtaWarga</a>
            <a href="#" class="brand-bell" aria-label="Notifikasi">
                @include('partials.icon', ['name' => 'bell'])
                <span class="dot"></span>
            </a>
        </div>

        <div class="role-badge">
            <span class="pulse"></span>
            Masuk sebagai <strong>{{ $isBpbd ? 'BPBD' : 'Ketua RT' }}</strong>
        </div>

        <nav class="nav">
            @foreach ($menu as $item)
                <a href="{{ $item['route'] ? route($item['route']) : '#' }}" class="{{ $item['route'] && request()->routeIs($item['route']) ? 'active' : '' }}">
                    @include('partials.icon', ['name' => $item['icon']])
                    {{ $item['label'] }}
                    @if (! empty($item['badge']))
                        <span class="count">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="sidebar-foot">
            <a href="#" class="help">@include('partials.icon', ['name' => 'help']) Bantuan</a>

            <div class="user-card">
                <div class="avatar {{ $isBpbd ? '' : 'rt' }}">{{ $initials }}</div>
                <div class="user-meta">
                    <strong title="{{ $user->name }}">{{ $user->name }}</strong>
                    <span>{{ $user->email }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" title="Keluar" aria-label="Keluar">
                        @include('partials.icon', ['name' => 'logout'])
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="main">
        @yield('content')
    </main>
</div>
</body>
</html>