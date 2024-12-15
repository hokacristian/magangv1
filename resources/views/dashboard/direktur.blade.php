<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Direktur</title>
</head>
<body>
    <h1>Selamat datang, Direktur!</h1>
    <p>Ini adalah halaman dashboard untuk Direktur.</p>

    <!-- Tombol Download PDF -->
    <a href="{{ route('direktur.download-pdf') }}">
        <button type="button">Download Laporan PDF</button>
    </a>

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>
