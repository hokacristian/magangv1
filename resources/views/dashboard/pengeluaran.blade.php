<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengeluaran</title>
</head>
<body>
    <h1>Selamat datang, Pengeluaran!</h1>

    <h2>Input Data Pengeluaran</h2>
    <form action="{{ route('pengeluaran.store') }}" method="POST">
        @csrf
        <label for="rekening_id">Pilih Rekening:</label>
        <select name="rekening_id" id="rekening_id" required>
            <option value="" disabled selected>Pilih Rekening</option>
            @foreach($rekenings as $rekening)
                <option value="{{ $rekening->id }}">{{ $rekening->rekening }} - {{ $rekening->bank }}</option>
            @endforeach
        </select><br>

        <p><strong>Saldo Saat Ini:</strong> <span id="saldo_saat_ini">0</span></p>

        <label for="bulan">Bulan:</label>
        <select name="bulan" required>
            @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                <option value="{{ $bulan }}">{{ $bulan }}</option>
            @endforeach
        </select><br>

        <label for="jumlah_pengeluaran">Jumlah Pengeluaran:</label>
        <input type="number" name="jumlah_pengeluaran" step="0.01" required><br>

        <label for="keterangan">Keterangan:</label>
        <input type="text" name="keterangan" required><br>

        <label for="status">Status:</label>
        <select name="status" required>
            <option value="Sudah Disahkan">Sudah Disahkan</option>
            <option value="Belum Disahkan">Belum Disahkan</option>
        </select><br>

        <button type="submit">Simpan</button>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#rekening_id').change(function () {
                const rekeningId = $(this).val();
                if (rekeningId) {
                    $.ajax({
                        url: '/rekening/saldo/' + rekeningId,
                        type: 'GET',
                        success: function (data) {
                            $('#saldo_saat_ini').text(data.saldo_saat_ini.toLocaleString('id-ID'));
                        },
                        error: function () {
                            alert('Gagal mendapatkan saldo rekening.');
                        }
                    });
                } else {
                    $('#saldo_saat_ini').text('0');
                }
            });
        });
    </script>

    <h2>Riwayat Pengeluaran</h2>
    <table border="1">
        <tr>
            <th>Bulan</th>
            <th>Rekening</th>
            <th>Saldo Awal</th>
            <th>Jumlah Pengeluaran</th>
            <th>Saldo Akhir</th>
            <th>Keterangan</th>
            <th>Status</th>
        </tr>
        @foreach($pengeluarans as $pengeluaran)
        <tr>
            <td>{{ $pengeluaran->bulan }}</td>
            <td>{{ $pengeluaran->rekening->rekening }} - {{ $pengeluaran->rekening->bank }}</td>
            <td>{{ number_format($pengeluaran->saldo_awal, 2) }}</td>
            <td>{{ number_format($pengeluaran->jumlah_pengeluaran, 2) }}</td>
            <td>{{ number_format($pengeluaran->saldo_akhir, 2) }}</td>
            <td>{{ $pengeluaran->keterangan }}</td>
            <td>
                <form action="{{ route('pengeluaran.updateStatus', $pengeluaran->id) }}" method="POST">
                    @csrf
                    <select name="status" onchange="this.form.submit()">
                        <option value="Sudah Disahkan" {{ $pengeluaran->status === 'Sudah Disahkan' ? 'selected' : '' }}>Sudah Disahkan</option>
                        <option value="Belum Disahkan" {{ $pengeluaran->status === 'Belum Disahkan' ? 'selected' : '' }}>Belum Disahkan</option>
                    </select>
                </form>
            </td>
        </tr>
        @endforeach
    </table>

    <h3>Belum Disahkan</h3>
<table border="1">
    <tr>
        <th>Bulan</th>
        <th>Rekening</th>
        <th>Saldo Awal</th>
        <th>Jumlah Pengeluaran</th>
        <th>Keterangan</th>
        <th>Status</th>
    </tr>
    @foreach($belumDisahkan as $data)
    <tr>
        <td>{{ $data->bulan }}</td>
        <td>{{ $data->rekening->rekening }} - {{ $data->rekening->bank }}</td>
        <td>{{ number_format($data->saldo_awal, 2) }}</td>
        <td>{{ number_format($data->jumlah_pengeluaran, 2) }}</td>
        <td>{{ $data->keterangan }}</td>
        <td>{{ $data->status }}</td>
    </tr>
    @endforeach
</table>

</body>
</html>
