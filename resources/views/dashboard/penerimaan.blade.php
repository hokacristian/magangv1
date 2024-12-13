<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penerimaan</title>
</head>
<body>
    <h1>Selamat datang, Penerimaan!</h1>
    <p>Ini adalah halaman dashboard untuk Penerimaan.</p>
    <form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>
</body>
</html>
