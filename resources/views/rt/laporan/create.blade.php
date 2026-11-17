@extends('layouts.app')

@section('title', 'Kirim Laporan')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/rt-laporan-form.css') }}">

    @php
        use App\Http\Requests\StoreLaporanRequest as Rule;
    @endphp

    <a href="{{ route('rt.dashboard') }}" class="back-link">‹ Kembali ke Ringkasan</a>

    <div class="page-head">
        <div>
            {{-- PBI-01: form isian alamat dan jumlah terdampak --}}
            <h1 class="page-title">Kirim Laporan Paceklik Air</h1>
            <p class="page-sub">Isi alamat wilayah dan jumlah KK yang terdampak. Laporan langsung diteruskan ke BPBD tanpa perlu mengantar berkas.</p>
        </div>
    </div>

    <div class="card report-form">
        @if ($errors->any())
            <div class="alert err" role="alert">
                <span>Laporan belum bisa dikirim. Periksa kembali isian yang ditandai di bawah.</span>
            </div>
        @endif

        {{-- PBI-02: validasi utama ada di server (StoreLaporanRequest); atribut required hanya pelengkap di browser --}}
        <form method="POST" action="{{ route('rt.laporan.store') }}" id="form-laporan">
            @csrf

            <div class="field {{ $errors->has('alamat') ? 'invalid' : '' }}">
                <label for="alamat">Alamat Lengkap RT / RW <span class="req">*</span></label>
                <textarea id="alamat" name="alamat" rows="3"
                          required minlength="{{ Rule::ALAMAT_MIN }}" maxlength="{{ Rule::ALAMAT_MAX }}"
                          aria-invalid="{{ $errors->has('alamat') ? 'true' : 'false' }}"
                          @error('alamat') aria-describedby="alamat-error" autofocus @enderror
                          placeholder="Contoh: Jl. Antapani Lama No. 12, RT 05 / RW 09">{{ old('alamat') }}</textarea>
                @error('alamat')
                    <div class="err" id="alamat-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field {{ $errors->has('jumlah_kk_terdampak') ? 'invalid' : '' }}">
                <label for="jumlah_kk_terdampak">Jumlah KK Terdampak <span class="req">*</span></label>
                <input type="number" id="jumlah_kk_terdampak" name="jumlah_kk_terdampak"
                       value="{{ old('jumlah_kk_terdampak') }}"
                       required min="1" max="{{ Rule::KK_MAX }}" step="1" inputmode="numeric"
                       aria-invalid="{{ $errors->has('jumlah_kk_terdampak') ? 'true' : 'false' }}"
                       @if ($errors->has('jumlah_kk_terdampak') && ! $errors->has('alamat')) autofocus @endif
                       aria-describedby="{{ $errors->has('jumlah_kk_terdampak') ? 'kk-error' : 'kk-hint' }}"
                       placeholder="Contoh: 42">
                <div class="hint" id="kk-hint">Jumlah kepala keluarga di wilayah Anda yang kesulitan mendapat air bersih.</div>
                @error('jumlah_kk_terdampak')
                    <div class="err" id="kk-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field {{ $errors->has('keterangan') ? 'invalid' : '' }}">
                <label for="keterangan">Catatan Tambahan <span class="optional">(opsional)</span></label>
                <textarea id="keterangan" name="keterangan" rows="3" maxlength="{{ Rule::KETERANGAN_MAX }}"
                          aria-invalid="{{ $errors->has('keterangan') ? 'true' : 'false' }}"
                          @error('keterangan') aria-describedby="keterangan-error" @enderror
                          placeholder="Contoh: Ada lansia dan balita yang butuh air bersih untuk minum dan sanitasi.">{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <div class="err" id="keterangan-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('rt.dashboard') }}" class="btn-ghost">Batal</a>
                <button type="submit" class="btn-primary">
                    @include('partials.icon', ['name' => 'send']) Kirim Laporan
                </button>
            </div>
        </form>
    </div>

    {{-- PBI-02: pesan validasi browser dalam bahasa Indonesia --}}
    <script>
        (function () {
            var pesan = {
                alamat: {
                    valueMissing: 'Alamat lengkap wajib diisi.',
                    tooShort: 'Alamat terlalu pendek, tulis minimal {{ Rule::ALAMAT_MIN }} karakter.'
                },
                jumlah_kk_terdampak: {
                    valueMissing: 'Jumlah KK terdampak wajib diisi.',
                    badInput: 'Isi dengan angka.',
                    stepMismatch: 'Jumlah KK terdampak harus berupa angka bulat.',
                    rangeUnderflow: 'Jumlah KK terdampak minimal 1 KK.',
                    rangeOverflow: 'Jumlah KK terdampak maksimal {{ number_format(Rule::KK_MAX, 0, ',', '.') }} KK.'
                }
            };

            document.querySelectorAll('#form-laporan [name]').forEach(function (el) {
                var daftar = pesan[el.name];
                if (!daftar) return;

                el.addEventListener('invalid', function () {
                    el.setCustomValidity('');
                    for (var kunci in daftar) {
                        if (el.validity[kunci]) { el.setCustomValidity(daftar[kunci]); break; }
                    }
                });
                el.addEventListener('input', function () { el.setCustomValidity(''); });
            });

            // Spasi saja dianggap kosong (server juga menolaknya).
            var alamat = document.getElementById('alamat');
            document.getElementById('form-laporan').addEventListener('submit', function (e) {
                if (alamat.value.trim() === '') {
                    e.preventDefault();
                    alamat.value = '';
                    alamat.reportValidity();
                }
            });
        })();
    </script>
@endsection