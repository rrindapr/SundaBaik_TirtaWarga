@extends('layouts.app')

@section('title', 'Detail Penetapan ' . $penetapan->nomor_surat_tugas)

@section('content')
    {{-- PBI-23: style dipisah agar tidak mengubah tirtawarga.css --}}
    <link rel="stylesheet" href="{{ asset('css/bpbd-penetapan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bpbd-penetapan-detail.css') }}">

    @php
        $statusBantuanClasses = [
            'Aktif'            => 'ditetapkan',
            'Dalam Pengiriman' => 'diverifikasi',
            'Selesai'          => 'selesai',
            'Dibatalkan'       => 'dibatalkan',
        ];
        $statusLaporanLabels  = \App\Models\LaporanPaceklik::STATUS_LABELS;
        $statusLaporanClasses = [1 => 'menunggu', 2 => 'diverifikasi', 3 => 'selesai'];
        $severityClasses = ['Kritis' => 'kritis', 'Sedang' => 'sedang', 'Rendah' => 'rendah'];
        $angka = fn ($nilai) => number_format((int) $nilai, 0, ',', '.');

        if ($selisihHari > 0) {
            $keteranganJadwal = $selisihHari . ' hari lagi';
        } elseif ($selisihHari === 0) {
            $keteranganJadwal = 'Hari ini';
        } else {
            $keteranganJadwal = abs($selisihHari) . ' hari yang lalu';
        }
    @endphp

    <a href="{{ route('bpbd.penetapan.index') }}" class="back-link">‹ Kembali ke Daftar Penetapan</a>

    <div class="page-head">
        <div>
            <h1 class="page-title mono">{{ $penetapan->nomor_surat_tugas }}</h1>
            <p class="page-sub">
                Ditetapkan {{ $penetapan->created_at->format('d/m/Y H.i') }} oleh {{ $penetapan->petugas_bpbd }}
            </p>
        </div>
        <span class="pill pill-lg {{ $statusBantuanClasses[$penetapan->status_bantuan] ?? '' }}">
            {{ $penetapan->status_bantuan }}
        </span>
    </div>

    {{-- Ringkasan cepat --}}
    <div class="kpi-row">
        <div class="kpi">
            <div class="kpi-label">Warga terdampak</div>
            <div class="kpi-value">{{ $angka($laporan->jumlah_kk_terdampak ?? 0) }} KK</div>
            <div class="kpi-note">di wilayah ini</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Alokasi air</div>
            <div class="kpi-value">{{ $angka($penetapan->alokasi_tangki_liter) }} L</div>
            <div class="kpi-note">{{ $angka($penetapan->alokasi_tangki_liter / 5000) }} tangki @ 5.000 liter</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Jadwal distribusi</div>
            <div class="kpi-value">{{ $jadwal->format('d/m/Y') }}</div>
            <div class="kpi-note">{{ $keteranganJadwal }}</div>
        </div>
    </div>

    {{-- Data wilayah terpilih (dari laporan paceklik) --}}
    <section class="section">
        <div class="section-head" style="margin-bottom: 0">
            <h2 class="section-title">Wilayah Terpilih</h2>
        </div>
        <dl class="detail-grid card">
            <div>
                <dt>ID Laporan</dt>
                <dd class="mono">{{ $laporan->kode ?? '—' }}</dd>
            </div>
            <div>
                <dt>Tanggal Lapor</dt>
                {{-- PBI-11 --}}
                <dd class="mono">{{ $laporan?->tanggal_lapor?->format('d/m/Y H.i') ?? '—' }}</dd>
            </div>
            <div>
                <dt>Tingkat Keparahan</dt>
                <dd>
                    <span class="sev {{ $severityClasses[$laporan->tingkat_keparahan ?? ''] ?? 'sedang' }}">
                        {{ $laporan->tingkat_keparahan ?? 'Sedang' }}
                    </span>
                </dd>
            </div>
            <div>
                <dt>Status Laporan</dt>
                <dd>
                    <span class="pill {{ $statusLaporanClasses[$laporan->status ?? 0] ?? '' }}">
                        {{ $statusLaporanLabels[$laporan->status ?? 0] ?? 'Tidak diketahui' }}
                    </span>
                </dd>
            </div>
            <div class="wide">
                <dt>Alamat</dt>
                <dd>{{ $laporan->alamat ?? '—' }}</dd>
            </div>
            <div class="wide">
                <dt>Pelapor (Ketua RT)</dt>
                <dd>
                    {{ $laporan?->user?->name ?? 'Tidak diketahui' }}
                    @if ($laporan?->user?->email)
                        <small class="cell-note">{{ $laporan->user->email }}</small>
                    @endif
                </dd>
            </div>
            <div class="wide">
                <dt>Keterangan dari RT</dt>
                <dd>{{ $laporan?->keterangan ?: 'Tidak ada keterangan.' }}</dd>
            </div>
        </dl>
    </section>

    {{-- Titik lokasi wilayah (PBI-17) --}}
    <section class="section">
        <div class="section-head">
            <h2 class="section-title">Lokasi Wilayah</h2>
            @if ($koordinat)
                <span class="mono detail-coord">{{ number_format($koordinat['lat'], 6) }}, {{ number_format($koordinat['lng'], 6) }}</span>
            @endif
        </div>

        @if ($koordinat)
            <div id="detailPeta" class="detail-peta card" aria-label="Peta lokasi wilayah"></div>
        @else
            <div class="detail-peta-kosong">
                Laporan ini belum memiliki titik koordinat, sehingga lokasinya belum bisa ditampilkan di peta.
            </div>
        @endif
    </section>

    {{-- Data penetapan bantuan --}}
    <section class="section">
        <div class="section-head" style="margin-bottom: 0">
            <h2 class="section-title">Data Penetapan</h2>
        </div>
        <dl class="detail-grid card">
            <div>
                <dt>No. Surat Tugas</dt>
                <dd class="mono">{{ $penetapan->nomor_surat_tugas }}</dd>
            </div>
            <div>
                <dt>Tanggal Penetapan</dt>
                <dd class="mono">{{ $penetapan->created_at->format('d/m/Y H.i') }}</dd>
            </div>
            <div>
                <dt>Petugas BPBD</dt>
                <dd>{{ $penetapan->petugas_bpbd }}</dd>
            </div>
            <div>
                <dt>Status Bantuan</dt>
                <dd>{{ $penetapan->status_bantuan }}</dd>
            </div>
            <div>
                <dt>Alokasi Air</dt>
                <dd class="mono">{{ $angka($penetapan->alokasi_tangki_liter) }} liter</dd>
            </div>
            <div>
                <dt>Jadwal Distribusi</dt>
                <dd class="mono">{{ $jadwal->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt>Terakhir Diperbarui</dt>
                <dd class="mono">{{ $penetapan->updated_at?->format('d/m/Y H.i') ?? '—' }}</dd>
            </div>
            <div>
                <dt>ID Penetapan</dt>
                <dd class="mono">#{{ $penetapan->id }}</dd>
            </div>
            <div class="wide">
                <dt>Catatan Verifikasi</dt>
                <dd>{{ $penetapan->catatan_verifikasi ?: 'Tidak ada catatan.' }}</dd>
            </div>
        </dl>
    </section>

    {{-- Riwayat status laporan (PBI-34) --}}
    <section class="section">
        <h2 class="section-title">Riwayat Status Laporan</h2>
        @if ($laporan && $laporan->logStatus->isNotEmpty())
            <ol class="detail-log card">
                @foreach ($laporan->logStatus as $log)
                    <li>
                        <span class="pill {{ $statusLaporanClasses[$log->status_baru] ?? '' }}">
                            {{ $statusLaporanLabels[$log->status_baru] ?? 'Tidak diketahui' }}
                        </span>
                        <div>
                            <strong>{{ $log->keterangan ?: 'Perubahan status' }}</strong>
                            <small class="mono">
                                {{ $log->created_at->format('d/m/Y H.i') }}
                                @if ($log->petugas)
                                    · {{ $log->petugas }}
                                @endif
                            </small>
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="page-sub">Belum ada riwayat status.</p>
        @endif
    </section>

    {{-- Penetapan lain untuk wilayah yang sama --}}
    <section class="section" style="border-bottom: 0">
        <h2 class="section-title">Riwayat Penetapan Wilayah Ini</h2>
        @if ($riwayatPenetapan->isEmpty())
            <p class="page-sub">Belum ada penetapan lain untuk wilayah ini.</p>
        @else
            <div class="card table-wrap" style="margin-top: 18px">
                <table class="data">
                    <thead>
                        <tr>
                            <th>No. Surat Tugas</th>
                            <th>Tanggal Penetapan</th>
                            <th>Alokasi</th>
                            <th>Petugas</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayatPenetapan as $lain)
                            <tr>
                                <td class="mono"><strong>{{ $lain->nomor_surat_tugas }}</strong></td>
                                <td class="mono">{{ $lain->created_at->format('d/m/Y H.i') }}</td>
                                <td class="mono">{{ $angka($lain->alokasi_tangki_liter) }} liter</td>
                                <td>{{ $lain->petugas_bpbd }}</td>
                                <td>
                                    <span class="pill {{ $statusBantuanClasses[$lain->status_bantuan] ?? '' }}">{{ $lain->status_bantuan }}</span>
                                </td>
                                <td><a href="{{ route('bpbd.penetapan.show', $lain) }}" class="link">Lihat detail ›</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if ($koordinat)
        <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
        <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
        <script>
            (function () {
                const titik = @json($koordinat);
                const peta = L.map('detailPeta', { scrollWheelZoom: false }).setView([titik.lat, titik.lng], 16);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(peta);

                L.circleMarker([titik.lat, titik.lng], {
                    radius: 10, color: '#2f6fd6', weight: 3, fillColor: '#2f6fd6', fillOpacity: 0.35
                }).addTo(peta).bindPopup(@json(($laporan->kode ?? '') . ' · ' . ($laporan->alamat ?? ''))).openPopup();
            })();
        </script>
    @endif
@endsection