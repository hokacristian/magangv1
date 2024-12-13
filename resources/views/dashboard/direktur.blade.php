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
    <form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>
</body>
</html>
