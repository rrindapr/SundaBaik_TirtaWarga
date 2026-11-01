<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · TirtaWarga</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/tirtawarga.css') }}">
</head>
<body>
<div class="auth">
    <aside class="auth-side">
        <div class="brand-logo">@include('partials.logo') TirtaWarga</div>

        <div>
            <h1>Bantuan air bersih,<br>sampai lebih cepat.</h1>
            <p>Ketua RT melaporkan krisis air langsung dari web, dan BPBD menentukan prioritas wilayah berdasarkan peta krisis, tanpa berkas fisik dan antrean birokrasi.</p>
            <div class="auth-stats">
                <div><strong>&lt; 24 jam</strong><span>target waktu tanggap</span></div>
                <div><strong>3–7 hari</strong><span>proses manual sebelumnya</span></div>
            </div>
        </div>

        <div class="auth-foot">Mendukung SDG 6 · Air Bersih dan Sanitasi Layak</div>
    </aside>

    <main class="auth-main">
        <form method="POST" action="{{ route('login.attempt') }}" class="auth-form" novalidate>
            @csrf
            <h2>Masuk</h2>
            <p>Gunakan akun yang diberikan oleh admin TirtaWarga.</p>

            @if (session('status'))
                <div class="alert ok">{{ session('status') }}</div>
            @endif

            <div class="field {{ $errors->has('email') ? 'invalid' : '' }}">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@tirtawarga.test" autocomplete="username" autofocus required>
                @if ($errors->has('email'))
                    <div class="err">{{ $errors->first('email') }}</div>
                @endif
            </div>

            <div class="field {{ $errors->has('password') ? 'invalid' : '' }}">
                <label for="password">Kata sandi</label>
                <input id="password" type="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                @if ($errors->has('password'))
                    <div class="err">{{ $errors->first('password') }}</div>
                @endif
            </div>

            <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya</label>

            <button type="submit" class="btn-primary">@include('partials.icon', ['name' => 'lock']) Masuk</button>

            @if (app()->environment('local'))
                <div class="demo">
                    <strong>Akun uji (hanya tampil di mode local)</strong><br>
                    BPBD: <code>bpbd@tirtawarga.com</code><br>
                    Ketua RT: <code>rtantapani@tirtawarga.com</code><br>
                    Kata sandi: <code>tirtawarga123</code>
                </div>
            @endif
        </form>
    </main>
</div>
</body>
</html>