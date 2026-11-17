@extends('layouts.app')

@section('title', 'Kirim Laporan')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/rt-laporan-form.css') }}">

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
            <div class="alert err">
                <span>Laporan belum bisa dikirim. Periksa kembali isian di bawah.</span>
            </div>
        @endif

        <form method="POST" action="{{ route('rt.laporan.store') }}">
            @csrf

            <div class="field {{ $errors->has('alamat') ? 'invalid' : '' }}">
                <label for="alamat">Alamat Lengkap RT / RW <span class="req">*</span></label>
                <textarea id="alamat" name="alamat" rows="3" maxlength="255"
                          placeholder="Contoh: Jl. Antapani Lama No. 12, RT 05 / RW 09">{{ old('alamat') }}</textarea>
                @error('alamat')
                    <div class="err">{{ $message }}</div>
                @enderror
            </div>

            <div class="field {{ $errors->has('jumlah_kk_terdampak') ? 'invalid' : '' }}">
                <label for="jumlah_kk_terdampak">Jumlah KK Terdampak <span class="req">*</span></label>
                <input type="number" id="jumlah_kk_terdampak" name="jumlah_kk_terdampak"
                       value="{{ old('jumlah_kk_terdampak') }}" min="1" step="1" inputmode="numeric"
                       placeholder="Contoh: 42">
                <div class="hint">Jumlah kepala keluarga di wilayah Anda yang kesulitan mendapat air bersih.</div>
                @error('jumlah_kk_terdampak')
                    <div class="err">{{ $message }}</div>
                @enderror
            </div>

            <div class="field {{ $errors->has('keterangan') ? 'invalid' : '' }}">
                <label for="keterangan">Catatan Tambahan <span class="optional">(opsional)</span></label>
                <textarea id="keterangan" name="keterangan" rows="3"
                          placeholder="Contoh: Ada lansia dan balita yang butuh air bersih untuk minum dan sanitasi.">{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <div class="err">{{ $message }}</div>
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
@endsection