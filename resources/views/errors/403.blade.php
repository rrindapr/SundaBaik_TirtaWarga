<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses ditolak · TirtaWarga</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/tirtawarga.css') }}">
</head>
<body>
<div class="error-page">
    <div>
        <div class="code">403</div>
        <h1>Akses ditolak</h1>
        <p>{{ $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.' }}</p>
        <a href="{{ url('/') }}" class="btn-primary">Kembali ke beranda</a>
    </div>
</div>
</body>
</html>