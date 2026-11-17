{{-- PBI-03: konfirmasi berhasil / gagal setelah Ketua RT mengirim laporan.
     Dipakai di rt/dashboard (konfirmasi berhasil) dan rt/laporan/create (pesan gagal).
     Flash session hanya hidup satu request, jadi banner hilang saat halaman di-refresh. --}}
@php
    $kirim = session('laporan_terkirim');
    $gagal = session('laporan_gagal');
    $kelasStatus = [1 => 'menunggu', 2 => 'diverifikasi', 3 => 'selesai'];
@endphp

@if ($kirim || $gagal)
    <link rel="stylesheet" href="{{ asset('css/rt-laporan-konfirmasi.css') }}">
@endif

@if ($kirim)
    <div class="konfirmasi" role="status" aria-live="polite">
        <div class="konfirmasi-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
        </div>

        <div class="konfirmasi-body">
            <h2>Laporan berhasil dikirim</h2>
            <p>Laporan Anda sudah tersimpan dan diteruskan ke BPBD. Simpan ID laporan di bawah untuk memantau statusnya.</p>

            <dl class="konfirmasi-data">
                <div>
                    <dt>ID Laporan</dt>
                    <dd class="mono konfirmasi-kode">
                        {{ $kirim['kode'] }}
                        <button type="button" class="konfirmasi-salin" data-kode="{{ $kirim['kode'] }}">Salin ID</button>
                    </dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd><span class="pill {{ $kelasStatus[$kirim['status']] ?? '' }}">{{ $kirim['status_label'] }}</span></dd>
                </div>
                <div>
                    <dt>Dikirim</dt>
                    <dd class="mono">{{ $kirim['tanggal'] }}</dd>
                </div>
                <div>
                    <dt>Terdampak</dt>
                    <dd class="mono">{{ $kirim['jumlah_kk'] }} KK</dd>
                </div>
            </dl>

            <div class="konfirmasi-actions">
                <a href="{{ route('rt.laporan.show', $kirim['kode']) }}" class="btn-primary">Lihat detail laporan</a>
                <a href="{{ route('rt.laporan.create') }}" class="btn-ghost">Kirim laporan lain</a>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.konfirmasi-salin').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                if (!navigator.clipboard) return;
                navigator.clipboard.writeText(tombol.dataset.kode).then(function () {
                    tombol.textContent = 'Tersalin';
                    setTimeout(function () { tombol.textContent = 'Salin ID'; }, 2000);
                });
            });
        });
    </script>
@endif

@if ($gagal)
    <div class="alert err" role="alert" style="margin-top: 24px">
        <span>{{ $gagal }}</span>
    </div>
@endif