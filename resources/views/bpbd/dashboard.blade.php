@extends('layouts.app')

@section('title', 'Ringkasan BPBD')

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Ringkasan</h1>
            <p class="page-sub">Pantau laporan paceklik air dan tentukan prioritas penyaluran bantuan.</p>
        </div>
    </div>

    <nav class="tabs">
        <a href="#" class="active">Ringkasan</a>
        <a href="#">Laporan Masuk</a>
        <a href="#">Peta Krisis</a>
        <a href="#">Penetapan</a>
    </nav>

    <div class="toolbar">
        <div class="chips">
            <button type="button" class="chip on">@include('partials.icon', ['name' => 'calendar']) Minggu ini</button>
            <button type="button" class="chip">@include('partials.icon', ['name' => 'pin']) Kecamatan</button>
            <button type="button" class="chip">@include('partials.icon', ['name' => 'users']) Ketua RT</button>
            <button type="button" class="chip">@include('partials.icon', ['name' => 'status']) Status</button>
        </div>
        <div class="actions">
            <a href="#" class="action">Ekspor @include('partials.icon', ['name' => 'export'])</a>
            <a href="#" class="action">Simpan laporan @include('partials.icon', ['name' => 'bookmark'])</a>
        </div>
    </div>

    <div class="kpi-row">
        @foreach ($mainStats as $stat)
            <div class="kpi">
                <div class="kpi-label">{{ $stat['label'] }}</div>
                <div class="kpi-value mono">{{ $stat['value'] }}</div>
                <div class="kpi-note"><span class="{{ $stat['isGood'] ? 'up' : 'down' }}">{{ $stat['change'] }}</span> dibanding minggu lalu</div>
            </div>
        @endforeach
    </div>

    <div class="kpi-row flat">
        @foreach ($statusStats as $stat)
            <div class="kpi">
                <div class="kpi-label">Status: {{ $stat['label'] }}</div>
                <div class="kpi-value mono">{{ $stat['value'] }} <span style="font-size:18px;color:var(--muted);font-weight:600">laporan</span></div>
                <div class="kpi-note"><span class="{{ $stat['isGood'] ? 'up' : 'down' }}">{{ $stat['change'] }}</span> dibanding minggu lalu</div>
            </div>
        @endforeach
    </div>

    <section class="section">
        <div class="section-head">
            <h2 class="section-title">Minggu Ini</h2>
            <div class="legend">
                <span><i style="background:var(--bar-1)"></i> Laporan masuk</span>
                <span><i style="background:var(--bar-2)"></i> Selesai ditangani</span>
            </div>
        </div>

        <div class="chart">
            <div class="chart-y">
                @for ($y = $chartMax; $y >= 0; $y -= $chartMax / 5)
                    <span>{{ $y }}</span>
                @endfor
            </div>
            <div class="chart-plot">
                <div class="chart-grid">
                    @for ($i = 0; $i < 6; $i++)
                        <div></div>
                    @endfor
                </div>
                <div class="chart-cols">
                    @foreach ($weeklyChart as $day)
                        <div class="chart-col">
                            <div class="bar b1" style="height: {{ $day['received'] / $chartMax * 100 }}%" data-v="{{ $day['received'] }} masuk"></div>
                            <div class="bar b2" style="height: {{ $day['completed'] / $chartMax * 100 }}%" data-v="{{ $day['completed'] }} selesai"></div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="chart-x">
                @foreach ($weeklyChart as $day)
                    <div>{{ $day['day'] }}<small>{{ $day['received'] }} laporan</small></div>
                @endforeach
            </div>
        </div>
    </section>

    @php
        // Warna tiap wilayah + potongan diagram donat (conic-gradient).
        $colors = ['coral' => 'var(--coral)', 'blue' => 'var(--blue)', 'green' => 'var(--green)', 'pink' => 'var(--pink)'];
        $start = 0;
        $slices = [];
        foreach ($areas as $area) {
            $end = $start + $area['percent'];
            $slices[] = $colors[$area['color']] . ' ' . $start . '% ' . $end . '%';
            $start = $end;
        }
        $topArea = $areas[0] ?? null;
    @endphp

    <div class="split">
        <div>
            <div class="selects">
                <span class="select">Kelompokkan: <strong>Kelurahan</strong> @include('partials.icon', ['name' => 'chevron'])</span>
                <span class="select">Tampilkan: <strong>Terdampak</strong> @include('partials.icon', ['name' => 'chevron'])</span>
            </div>
            <div class="card table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>↓ Wilayah</th>
                            <th>↓ %</th>
                            <th>↓ Terdampak</th>
                            <th>↓ Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($areas as $area)
                            <tr>
                                <td>
                                    <div class="cell-user">
                                        <span class="ring {{ $area['color'] }}">{{ $area['reports'] }}</span>
                                        <div>{{ $area['name'] }}<small>Kec. {{ $area['district'] }}</small></div>
                                    </div>
                                </td>
                                <td class="mono">{{ $area['percent'] }}%</td>
                                <td class="mono">{{ number_format($area['affected'], 0, ',', '.') }} KK</td>
                                <td><span class="pill {{ strtolower($area['status']) }}">{{ $area['status'] }}</span></td>
                                <td class="more">···</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card donut-card">
            @if ($topArea)
                <div class="donut-tip">
                    <div class="who"><span class="avatar">{{ strtoupper(substr($topArea['name'], 0, 2)) }}</span> {{ $topArea['name'] }}</div>
                    <div class="row"><span>Total terdampak</span><strong class="mono">{{ number_format($topArea['affected'], 0, ',', '.') }} KK</strong></div>
                </div>
            @endif
            <div class="donut-wrap">
                <div class="donut" style="background: conic-gradient({{ implode(', ', $slices) }});"></div>
                <div class="donut-legend">
                    @foreach ($areas as $area)
                        <span><i style="background: {{ $colors[$area['color']] }}"></i> {{ $area['name'] }} <b class="mono">{{ $area['percent'] }}%</b></span>
                    @endforeach
                    <span style="margin-top:6px;color:var(--muted)">Total <b class="mono" style="color:var(--ink)">{{ number_format($totalAffected, 0, ',', '.') }} KK</b></span>
                </div>
            </div>
        </div>
    </div>
@endsection