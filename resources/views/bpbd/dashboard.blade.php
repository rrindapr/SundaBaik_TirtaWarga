<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TirtaWarga - Dashboard BPBD Penanggulangan Krisis Air (SDG 6)</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sdg-badge {
            background-color: #26bde2;
            color: white;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        /* PBI-19: Efek Sorotan Warna Baris */
        .table-row-selected {
            background-color: #d1ecf1 !important;
            border-left: 5px solid #0c5460 !important;
            transition: all 0.2s ease-in-out;
        }
        .card-table {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="#">
            <i class="bi bi-droplet-fill text-info"></i>
            <span>TirtaWarga</span>
            <span class="sdg-badge ms-2"><i class="bi bi-globe"></i> SDG 6 Clean Water</span>
        </a>
        <div class="d-flex align-items-center text-white">
            <span class="me-3 small">Petugas BPBD: <strong>{{ $petugasAktif ?? auth()->user()->name ?? 'Marpaung Rinda Puspa Rani' }}</strong></span>
            <i class="bi bi-person-circle fs-4"></i>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1 text-gray-800">Daftar Laporan Krisis Air Masuk (PBI-13 & PBI-19)</h2>
            <p class="text-muted small mb-0">Verifikasi laporan ketua RT dan tetapkan alokasi armada tangki air bersih BPBD (PBI-20).</p>
        </div>
    </div>

    <!-- Alert Notifikasi Interaktif -->
    <div id="liveAlertBox" class="alert alert-dismissible fade d-none" role="alert">
        <span id="liveAlertMessage"></span>
        <button type="button" class="btn-close" aria-label="Close" onclick="hideAlert()"></button>
    </div>

    <!-- PBI-13: Tabel Laporan Masuk -->
    <div class="card card-table">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-table me-2"></i>Laporan Masuk dari Pengurus RT</h6>
            <span class="badge bg-light text-dark border">Total: {{ $daftarLaporan->count() }} Laporan</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabelLaporan">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">Kode</th>
                            <th scope="col">Pelapor (RT/RW)</th>
                            <th scope="col">Wilayah (Kelurahan / Kec.)</th>
                            <th scope="col" class="text-center">Terdampak</th>
                            <th scope="col" class="text-center">Tingkat Keparahan</th>
                            <th scope="col">Kondisi Lapangan</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-center pe-3">Aksi (PBI-19 & 20)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftarLaporan as $row)
                            <tr id="row-{{ $row->id }}" 
                                data-id="{{ $row->id }}"
                                data-kode="{{ $row->kode_laporan }}"
                                data-rt="RT {{ $row->nomor_rt }} / RW {{ $row->nomor_rw }}"
                                data-lokasi="Kel. {{ $row->kelurahan }}, Kec. {{ $row->kecamatan }}"
                                data-kk="{{ $row->jumlah_kk_terdampak }}"
                                data-status="{{ $row->status }}">
                                
                                <td class="ps-3 fw-bold text-primary">
                                    {{ $row->kode_laporan }}
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $row->nama_rt }}</div>
                                    <div class="text-muted small">RT {{ $row->nomor_rt }} / RW {{ $row->nomor_rw }}</div>
                                </td>
                                <td>
                                    <div>Kel. {{ $row->kelurahan }}</div>
                                    <div class="text-muted small">Kec. {{ $row->kecamatan }}</div>
                                </td>
                                <td class="text-center fw-bold">
                                    <span class="badge bg-secondary rounded-pill">{{ $row->jumlah_kk_terdampak }} KK</span>
                                </td>
                                <td class="text-center">
                                    @if ($row->tingkat_keparahan === 'Kritis')
                                        <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Kritis</span>
                                    @elseif ($row->tingkat_keparahan === 'Sedang')
                                        <span class="badge bg-warning text-dark">Sedang</span>
                                    @else
                                        <span class="badge bg-info text-dark">Rendah</span>
                                    @endif
                                </td>
                                <td class="small text-muted" style="max-width: 250px;">
                                    {{ $row->deskripsi_kondisi }}
                                </td>
                                <td class="text-center" id="status-badge-{{ $row->id }}">
                                    @if ($row->status === 'Ditetapkan')
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Ditetapkan</span>
                                    @elseif ($row->status === 'Menunggu')
                                        <span class="badge bg-secondary">Menunggu</span>
                                    @else
                                        <span class="badge bg-dark">{{ $row->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    <button type="button" 
                                            class="btn btn-sm {{ $row->status === 'Ditetapkan' ? 'btn-outline-secondary' : 'btn-primary' }} btn-action-tetapkan"
                                            onclick="handlePilihLaporan({{ $row->id }})">
                                        <i class="bi bi-check2-square me-1"></i> Pilih / Tetapkan
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada data laporan krisis air.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- PBI-20: Modal Form Konfirmasi Penetapan Wilayah -->
<div class="modal fade" id="modalPenetapan" tabindex="-1" aria-labelledby="modalPenetapanLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title h6" id="modalPenetapanLabel">
                    <i class="bi bi-shield-check me-2"></i>Konfirmasi Penetapan Penerima Bantuan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="removeHighlight()"></button>
            </div>
            <form id="formPenetapanBantuan" onsubmit="submitPenetapan(event)">
                <div class="modal-body">
                    <!-- Ringkasan Laporan PBI-19 -->
                    <div class="bg-light p-3 rounded mb-3 border">
                        <input type="hidden" id="modalLaporanId" name="laporan_id" value="">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted d-block">Kode Laporan:</span>
                                <strong id="modalKodeLaporan" class="text-primary">-</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Wilayah RT / RW:</span>
                                <strong id="modalWilayah">-</strong>
                            </div>
                            <div class="col-12 mt-2">
                                <span class="text-muted d-block">Lokasi:</span>
                                <span id="modalLokasi">-</span> (<strong id="modalKk">0</strong> KK)
                            </div>
                        </div>
                    </div>

                    <!-- Input Form Penetapan PBI-20 -->
                    <div class="mb-3">
                        <label for="petugasBpbd" class="form-label small fw-bold">Petugas Verifikator BPBD</label>
                        <input type="text" class="form-control form-control-sm" id="petugasBpbd" name="petugas_bpbd" value="{{ $petugasAktif }}" readonly>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="alokasiTangki" class="form-label small fw-bold">Alokasi Air (Liter) <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="alokasiTangki" name="alokasi_tangki_liter" required>
                                <option value="5000" selected>5.000 Liter (1 Tangki)</option>
                                <option value="10000">10.000 Liter (2 Tangki)</option>
                                <option value="15000">15.000 Liter (3 Tangki)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="jadwalDistribusi" class="form-label small fw-bold">Jadwal Distribusi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="jadwalDistribusi" name="jadwal_distribusi" required min="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="catatanVerifikasi" class="form-label small fw-bold">Catatan Verifikasi Lapangan</label>
                        <textarea class="form-control form-control-sm" id="catatanVerifikasi" name="catatan_verifikasi" rows="3" placeholder="Contoh: Akses jalan muat truk tangki, titik kumpul di balai RT."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" onclick="removeHighlight()">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btnSubmitModal">
                        <span id="btnSubmitSpinner" class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                        <i class="bi bi-save me-1" id="btnSubmitIcon"></i> Simpan & Tetapkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Logika JavaScript Interaktif PBI-19 & PBI-20 -->
<script>
    let modalInstance = null;
    let selectedRowElement = null;

    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('modalPenetapan');
        if (modalElement) {
            modalInstance = new bootstrap.Modal(modalElement);
        }

        // Set default jadwal ke hari esok
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const jadwalInput = document.getElementById('jadwalDistribusi');
        if (jadwalInput) {
            jadwalInput.value = tomorrow.toISOString().split('T')[0];
        }
    });

    // PBI-19: Sorot Baris Laporan
    function handlePilihLaporan(id) {
        removeHighlight();

        const row = document.getElementById(`row-${id}`);
        if (!row) return;

        row.classList.add('table-row-selected');
        selectedRowElement = row;

        const kode = row.dataset.kode;
        const rt = row.dataset.rt;
        const lokasi = row.dataset.lokasi;
        const kk = row.dataset.kk;
        const status = row.dataset.status;

        if (status === 'Ditetapkan') {
            showAlert('warning', `Perhatian: Laporan ${kode} sudah pernah ditetapkan sebelumnya.`);
        }

        document.getElementById('modalLaporanId').value = id;
        document.getElementById('modalKodeLaporan').textContent = kode;
        document.getElementById('modalWilayah').textContent = rt;
        document.getElementById('modalLokasi').textContent = lokasi;
        document.getElementById('modalKk').textContent = kk;

        if (modalInstance) {
            modalInstance.show();
        }
    }

    function removeHighlight() {
        if (selectedRowElement) {
            selectedRowElement.classList.remove('table-row-selected');
            selectedRowElement = null;
        }
    }

    // PBI-20: Kirim Data ke Laravel via AJAX
    async function submitPenetapan(event) {
        event.preventDefault();

        const form = document.getElementById('formPenetapanBantuan');
        const submitBtn = document.getElementById('btnSubmitModal');
        const spinner = document.getElementById('btnSubmitSpinner');
        const icon = document.getElementById('btnSubmitIcon');

        const laporanId = parseInt(document.getElementById('modalLaporanId').value);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const formData = {
            laporan_id: laporanId,
            petugas_bpbd: document.getElementById('petugasBpbd').value,
            alokasi_tangki_liter: parseInt(document.getElementById('alokasiTangki').value),
            jadwal_distribusi: document.getElementById('jadwalDistribusi').value,
            catatan_verifikasi: document.getElementById('catatanVerifikasi').value
        };

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        icon.classList.add('d-none');

        try {
            const response = await fetch("{{ route('bpbd.penetapan.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showAlert('success', `Sukses! ${result.message} (No. Surat: ${result.data.nomor_surat_tugas})`);
                updateTableRowStatus(laporanId, 'Ditetapkan');
                modalInstance.hide();
                form.reset();
                removeHighlight();
            } else {
                showAlert('danger', `Gagal: ${result.message}`);
                modalInstance.hide();
                removeHighlight();
            }
        } catch (error) {
            showAlert('danger', `Terjadi error jaringan: ${error.message}`);
        } finally {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            icon.classList.remove('d-none');
        }
    }

    function updateTableRowStatus(laporanId, newStatus) {
        const row = document.getElementById(`row-${laporanId}`);
        const badgeContainer = document.getElementById(`status-badge-${laporanId}`);
        if (!row || !badgeContainer) return;

        row.dataset.status = newStatus;

        if (newStatus === 'Ditetapkan') {
            badgeContainer.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Ditetapkan</span>';
            const actionBtn = row.querySelector('.btn-action-tetapkan');
            if (actionBtn) {
                actionBtn.classList.remove('btn-primary');
                actionBtn.classList.add('btn-outline-secondary');
            }
        }
    }

    function showAlert(type, message) {
        const alertBox = document.getElementById('liveAlertBox');
        const alertMessage = document.getElementById('liveAlertMessage');
        
        alertBox.className = `alert alert-${type} alert-dismissible fade show`;
        alertMessage.innerHTML = message;
        alertBox.classList.remove('d-none');

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function hideAlert() {
        document.getElementById('liveAlertBox').classList.add('d-none');
    }
</script>
</body>
</html>