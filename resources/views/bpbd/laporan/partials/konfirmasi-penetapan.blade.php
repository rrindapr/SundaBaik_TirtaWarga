{{--
    PBI-21: Konfirmasi penetapan bantuan.
    1. Setelah form penetapan (PBI-20) diisi, petugas melihat ringkasan dan wajib mencentang pernyataan.
    2. Penetapan baru dikirim ke server setelah petugas menekan "Ya, tetapkan".
    3. Setelah tersimpan, sistem menampilkan bukti penetapan (nomor surat tugas, dll.).
    Dipisah ke partial agar perubahan di bpbd/laporan/index.blade.php tetap sedikit.
--}}
<link rel="stylesheet" href="{{ asset('css/bpbd-konfirmasi-penetapan.css') }}">

{{-- Langkah 1: konfirmasi sebelum disimpan --}}
<dialog id="konfirmasiDialog" class="dialog" aria-labelledby="konfirmasiDialogTitle">
    <div class="dialog-head">
        <div>
            <h2 id="konfirmasiDialogTitle">Konfirmasi penetapan</h2>
            <p>Pastikan data berikut sudah benar. Wilayah yang sudah ditetapkan tidak bisa ditetapkan ulang selama penetapannya masih aktif.</p>
        </div>
        <button type="button" class="dialog-close" onclick="tutupKonfirmasiPenetapan()" aria-label="Tutup">×</button>
    </div>

    <dl class="dialog-summary">
        <div><dt>ID Laporan</dt><dd class="mono" id="konfKode">—</dd></div>
        <div><dt>Pelapor</dt><dd id="konfPelapor">—</dd></div>
        <div class="wide"><dt>Wilayah</dt><dd><span id="konfAlamat">—</span> · <span class="mono" id="konfTerdampak">0</span> KK</dd></div>
        <div><dt>Alokasi air</dt><dd id="konfAlokasi">—</dd></div>
        <div><dt>Jadwal distribusi</dt><dd class="mono" id="konfJadwal">—</dd></div>
        <div><dt>Petugas verifikator</dt><dd id="konfPetugas">—</dd></div>
        <div><dt>Status setelah disimpan</dt><dd><span class="pill ditetapkan">Ditetapkan</span></dd></div>
        <div class="wide"><dt>Catatan verifikasi</dt><dd id="konfCatatan">—</dd></div>
    </dl>

    <div class="dialog-body">
        <label class="konfirmasi-check">
            <input type="checkbox" id="konfirmasiSetuju" onchange="perbaruiTombolKonfirmasi()">
            <span>Saya menyatakan data penetapan di atas sudah benar dan wilayah ini ditetapkan sebagai penerima bantuan air bersih.</span>
        </label>
    </div>

    <div class="dialog-foot">
        <button type="button" class="btn-ghost" onclick="tutupKonfirmasiPenetapan()">‹ Kembali ubah</button>
        <button type="button" class="btn-primary" id="konfirmasiSubmit" onclick="kirimPenetapanTerkonfirmasi()" disabled>
            @include('partials.icon', ['name' => 'check']) Ya, tetapkan
        </button>
    </div>
</dialog>

{{-- Langkah 2: bukti penetapan setelah berhasil disimpan --}}
<dialog id="buktiDialog" class="dialog" aria-labelledby="buktiDialogTitle">
    <div class="bukti-head">
        <span class="bukti-ico">@include('partials.icon', ['name' => 'check'])</span>
        <h2 id="buktiDialogTitle">Penetapan berhasil dikonfirmasi</h2>
        <p>Wilayah telah ditetapkan sebagai penerima bantuan tangki air bersih.</p>
    </div>

    <dl class="dialog-summary">
        <div class="wide"><dt>No. Surat Tugas</dt><dd class="mono bukti-nomor" id="buktiNomor">—</dd></div>
        <div><dt>ID Laporan</dt><dd class="mono" id="buktiKode">—</dd></div>
        <div><dt>Ditetapkan pada</dt><dd class="mono" id="buktiTanggal">—</dd></div>
        <div class="wide"><dt>Wilayah</dt><dd><span id="buktiAlamat">—</span> · <span class="mono" id="buktiTerdampak">0</span> KK</dd></div>
        <div><dt>Alokasi air</dt><dd class="mono" id="buktiAlokasi">—</dd></div>
        <div><dt>Jadwal distribusi</dt><dd class="mono" id="buktiJadwal">—</dd></div>
        <div class="wide"><dt>Petugas BPBD</dt><dd id="buktiPetugas">—</dd></div>
    </dl>

    <div class="dialog-foot" style="margin-top: 20px">
        <button type="button" class="btn-ghost" onclick="document.getElementById('buktiDialog').close()">Tutup</button>
        <a href="#" class="btn-primary" id="buktiDetail" style="display: none">Lihat detail penetapan ›</a>
    </div>
</dialog>

<script>
    (function () {
        const konfirmasiDialog = document.getElementById('konfirmasiDialog');
        const setuju = document.getElementById('konfirmasiSetuju');
        const tombolKirim = document.getElementById('konfirmasiSubmit');

        function teks(id, nilai) {
            document.getElementById(id).textContent = (nilai === null || nilai === undefined || nilai === '') ? '—' : nilai;
        }

        function formatTanggal(isoDate) {
            // yyyy-mm-dd -> dd/mm/yyyy
            if (!isoDate) return '';
            const [y, m, d] = isoDate.split('-');
            return d + '/' + m + '/' + y;
        }

        function formatLiter(liter) {
            return Number(liter || 0).toLocaleString('id-ID') + ' liter';
        }

        // Dipanggil dari onsubmit form penetapan (PBI-20). Belum mengirim apa pun ke server.
        window.bukaKonfirmasiPenetapan = function (event) {
            event.preventDefault();

            const form = document.getElementById('assignForm');
            if (!form.reportValidity()) return;

            const alokasi = document.getElementById('waterAllocation');

            teks('konfKode', document.getElementById('dialogKode').textContent);
            teks('konfPelapor', document.getElementById('dialogReporter').textContent);
            teks('konfAlamat', document.getElementById('dialogAddress').textContent);
            teks('konfTerdampak', document.getElementById('dialogAffected').textContent);
            teks('konfAlokasi', alokasi.options[alokasi.selectedIndex].text);
            teks('konfJadwal', formatTanggal(document.getElementById('distributionDate').value));
            teks('konfPetugas', document.getElementById('officerName').value);
            teks('konfCatatan', document.getElementById('verificationNote').value.trim() || 'Tidak ada catatan.');

            setuju.checked = false;
            perbaruiTombolKonfirmasi();

            // Dialog konfirmasi tampil di atas dialog form, jadi isian tidak hilang saat "Kembali ubah".
            konfirmasiDialog.showModal();
        };

        window.perbaruiTombolKonfirmasi = function () {
            tombolKirim.disabled = !setuju.checked;
        };

        window.tutupKonfirmasiPenetapan = function () {
            konfirmasiDialog.close();
        };

        // "Ya, tetapkan": baru sekarang penetapan dikirim lewat fungsi PBI-20 (submitAssignment).
        window.kirimPenetapanTerkonfirmasi = function () {
            if (!setuju.checked) return;

            tombolKirim.disabled = true;
            konfirmasiDialog.close();
            submitAssignment({ preventDefault: function () {} });
        };

        // Dipanggil oleh submitAssignment setelah server berhasil menyimpan penetapan.
        window.tampilkanBuktiPenetapan = function (data) {
            if (!data) return;

            teks('buktiNomor', data.nomor_surat_tugas);
            teks('buktiKode', data.kode_laporan);
            teks('buktiTanggal', data.tanggal_penetapan);
            teks('buktiAlamat', data.alamat);
            teks('buktiTerdampak', data.jumlah_kk_terdampak);
            teks('buktiAlokasi', formatLiter(data.alokasi_tangki_liter));
            teks('buktiJadwal', data.jadwal_distribusi);
            teks('buktiPetugas', data.petugas_bpbd);

            const linkDetail = document.getElementById('buktiDetail');
            if (data.detail_url) {
                linkDetail.href = data.detail_url;
                linkDetail.style.display = 'inline-flex';
            } else {
                linkDetail.style.display = 'none';
            }

            document.getElementById('buktiDialog').showModal();
        };
    })();
</script>