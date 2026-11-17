@extends('layouts.app')

@section('title', 'Laporan Masuk')

@section('content')
    @php
        // Kolom status di database berupa angka (lihat migration laporan_paceklik).
        // "Ditetapkan" bukan status laporan: ditandai oleh adanya penetapan bantuan aktif (PBI-20).
        $statusLabels  = [1 => 'Menunggu', 2 => 'Diverifikasi', 3 => 'Selesai'];
        $statusClasses = [1 => 'menunggu', 2 => 'diverifikasi', 3 => 'selesai'];
        $severityClasses = ['Kritis' => 'kritis', 'Sedang' => 'sedang', 'Rendah' => 'rendah'];
    @endphp

    <div class="page-head">
        <div>
            <h1 class="page-title">Laporan Masuk</h1>
            <p class="page-sub">Laporan paceklik air dari seluruh Ketua RT. Pilih laporan untuk menetapkan wilayah penerima bantuan.</p>
        </div>
        <span class="count-badge">{{ count($daftarLaporan) }} laporan</span>
    </div>

    <div id="liveAlert" class="alert" style="display: none; margin-top: 24px" role="status">
        <span id="liveAlertMessage"></span>
        <button type="button" class="alert-close" onclick="hideAlert()" aria-label="Tutup">×</button>
    </div>

    @if (count($daftarLaporan) === 0)
        <div class="empty">
            <div class="ico">@include('partials.icon', ['name' => 'inbox'])</div>
            <h2>Belum ada laporan masuk</h2>
            <p>Laporan paceklik air yang dikirim Ketua RT akan muncul di sini.</p>
        </div>
    @else
        <div class="card table-wrap" style="margin-top: 30px">
            <table class="data">
                <thead>
                    <tr>
                        <th>ID Laporan</th>
                        <th>Pelapor</th>
                        <th>Alamat</th>
                        <th>Terdampak</th>
                        <th>Keparahan</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftarLaporan as $row)
                        @php
                            $isAssigned = $row->penetapanAktif !== null;
                            $reporterName = $row->user->name ?? 'Tidak diketahui';
                        @endphp
                        <tr id="row-{{ $row->id }}"
                            data-id="{{ $row->id }}"
                            data-kode="{{ $row->kode }}"
                            data-reporter="{{ $reporterName }}"
                            data-address="{{ $row->alamat }}"
                            data-affected="{{ $row->jumlah_kk_terdampak }}"
                            data-assigned="{{ $isAssigned ? '1' : '0' }}">
                            <td class="mono"><strong>{{ $row->kode }}</strong></td>
                            <td>
                                <div class="cell-stack">
                                    {{ $reporterName }}
                                    <small class="mono">{{ $row->created_at->format('d/m/Y H.i') }}</small>
                                </div>
                            </td>
                            <td class="cell-wrap">
                                {{ $row->alamat }}
                                @if ($row->keterangan)
                                    <small class="cell-note">{{ $row->keterangan }}</small>
                                @endif
                            </td>
                            <td class="mono">{{ $row->jumlah_kk_terdampak }} KK</td>
                            <td>
                                <span class="sev {{ $severityClasses[$row->tingkat_keparahan] ?? 'sedang' }}">
                                    {{ $row->tingkat_keparahan ?? 'Sedang' }}
                                </span>
                            </td>
                            <td id="status-{{ $row->id }}">
                                @if ($isAssigned)
                                    <span class="pill ditetapkan">Ditetapkan</span>
                                @else
                                    <span class="pill {{ $statusClasses[$row->status] ?? '' }}">
                                        {{ $statusLabels[$row->status] ?? 'Tidak diketahui' }}
                                    </span>
                                @endif
                            </td>
                            <td class="cell-action">
                                @if ($isAssigned)
                                    <span class="btn-sm done">@include('partials.icon', ['name' => 'check']) Sudah ditetapkan</span>
                                @else
                                    <button type="button" class="btn-sm" onclick="openAssignDialog({{ $row->id }})">
                                        @include('partials.icon', ['name' => 'flag']) Tetapkan
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- PBI-20: dialog konfirmasi penetapan wilayah penerima bantuan --}}
    <dialog id="assignDialog" class="dialog" aria-labelledby="assignDialogTitle">
        <form id="assignForm" onsubmit="submitAssignment(event)">
            <div class="dialog-head">
                <div>
                    <h2 id="assignDialogTitle">Tetapkan penerima bantuan</h2>
                    <p>Periksa data laporan, lalu tentukan alokasi dan jadwal distribusi air bersih.</p>
                </div>
                <button type="button" class="dialog-close" onclick="closeAssignDialog()" aria-label="Tutup">×</button>
            </div>

            <input type="hidden" id="dialogReportId" name="laporan_id">

            <dl class="dialog-summary">
                <div><dt>ID Laporan</dt><dd class="mono" id="dialogKode">—</dd></div>
                <div><dt>Pelapor</dt><dd id="dialogReporter">—</dd></div>
                <div class="wide"><dt>Alamat</dt><dd><span id="dialogAddress">—</span> · <span class="mono" id="dialogAffected">0</span> KK</dd></div>
            </dl>

            <div class="dialog-body">
                <div class="field">
                    <label for="officerName">Petugas verifikator</label>
                    <input type="text" id="officerName" value="{{ $petugasAktif }}" readonly>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="waterAllocation">Alokasi air</label>
                        <select id="waterAllocation" name="alokasi_tangki_liter" required>
                            <option value="5000" selected>5.000 liter (1 tangki)</option>
                            <option value="10000">10.000 liter (2 tangki)</option>
                            <option value="15000">15.000 liter (3 tangki)</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="distributionDate">Jadwal distribusi</label>
                        <input type="date" id="distributionDate" name="jadwal_distribusi" required min="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="field">
                    <label for="verificationNote">Catatan verifikasi <span class="optional">(opsional)</span></label>
                    <textarea id="verificationNote" name="catatan_verifikasi" rows="3" maxlength="1000" placeholder="Contoh: akses jalan muat truk tangki, titik kumpul di balai RT."></textarea>
                </div>
            </div>

            <div class="dialog-foot">
                <button type="button" class="btn-ghost" onclick="closeAssignDialog()">Batal</button>
                <button type="submit" class="btn-primary" id="assignSubmit">@include('partials.icon', ['name' => 'check']) Simpan &amp; tetapkan</button>
            </div>
        </form>
    </dialog>

    <script>
        const assignDialog = document.getElementById('assignDialog');
        let selectedRow = null;

        // PBI-19: pilih laporan dari daftar, sorot barisnya, lalu buka dialog penetapan.
        function openAssignDialog(id) {
            clearSelection();

            const row = document.getElementById('row-' + id);
            if (!row) return;

            row.classList.add('row-selected');
            selectedRow = row;

            document.getElementById('dialogReportId').value = id;
            document.getElementById('dialogKode').textContent = row.dataset.kode;
            document.getElementById('dialogReporter').textContent = row.dataset.reporter;
            document.getElementById('dialogAddress').textContent = row.dataset.address;
            document.getElementById('dialogAffected').textContent = row.dataset.affected;

            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('distributionDate').value = tomorrow.toISOString().split('T')[0];

            assignDialog.showModal();
        }

        function closeAssignDialog() {
            assignDialog.close();
        }

        function clearSelection() {
            if (selectedRow) {
                selectedRow.classList.remove('row-selected');
                selectedRow = null;
            }
        }

        assignDialog.addEventListener('close', clearSelection);

        // PBI-20: kirim penetapan ke server.
        async function submitAssignment(event) {
            event.preventDefault();

            const submitButton = document.getElementById('assignSubmit');
            const reportId = parseInt(document.getElementById('dialogReportId').value, 10);

            submitButton.disabled = true;

            try {
                const response = await fetch("{{ route('bpbd.penetapan.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        laporan_id: reportId,
                        alokasi_tangki_liter: parseInt(document.getElementById('waterAllocation').value, 10),
                        jadwal_distribusi: document.getElementById('distributionDate').value,
                        catatan_verifikasi: document.getElementById('verificationNote').value,
                    }),
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert('ok', result.message + ' Nomor surat tugas: ' + result.data.nomor_surat_tugas + '.');
                    markRowAssigned(reportId);
                    document.getElementById('assignForm').reset();
                } else {
                    const firstError = result.errors ? Object.values(result.errors)[0][0] : null;
                    showAlert('err', firstError || result.message || 'Penetapan gagal disimpan.');
                }
            } catch (error) {
                showAlert('err', 'Tidak dapat terhubung ke server. Coba lagi.');
            } finally {
                submitButton.disabled = false;
                closeAssignDialog();
            }
        }

        function markRowAssigned(id) {
            const row = document.getElementById('row-' + id);
            if (!row) return;

            row.dataset.assigned = '1';
            document.getElementById('status-' + id).innerHTML = '<span class="pill ditetapkan">Ditetapkan</span>';
            row.querySelector('.cell-action').innerHTML = '<span class="btn-sm done">Sudah ditetapkan</span>';
        }

        function showAlert(type, message) {
            const alertBox = document.getElementById('liveAlert');
            alertBox.className = 'alert ' + type;
            document.getElementById('liveAlertMessage').textContent = message;
            alertBox.style.display = 'flex';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function hideAlert() {
            document.getElementById('liveAlert').style.display = 'none';
        }
    </script>
@endsection