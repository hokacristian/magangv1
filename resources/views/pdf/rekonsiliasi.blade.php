<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekonsiliasi Saldo BLU</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }

        h1, h2, h3 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: center;
        }

        .section-title {
            margin-top: 30px;
            font-weight: bold;
        }

        .notes {
            margin-top: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h1>BERITA ACARA REKONSILIASI</h1>
    <h2>PEMERIKSAAN KAS SALDO REKENING BENDAHARA BLU</h2>
    <h2>BULAN {{ strtoupper($bulan) }} {{ $tahun }}</h2>
    <h3>POLITEKNIK KESEHATAN KEMENKES MANADO</h3>

    <p>Pada hari ini bulan {{ $bulan }} {{ $tahun }} telah dilakukan <strong>Rekonsiliasi Data Saldo Rekening BLU</strong> sebagai berikut:</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Rekening</th>
                <th>Bank</th>
                <th>Saldo Awal</th>
                <th>Penerimaan (Disahkan)</th>
                <th>Pengeluaran (Disahkan)</th>
                <th>Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataRekening as $index => $rek)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $rek['rekening'] }}</td>
                <td>{{ $rek['bank'] }}</td>
                <td>{{ number_format($rek['saldo_awal'], 2) }}</td>
                <td>{{ number_format($rek['penerimaan'], 2) }}</td>
                <td>{{ number_format($rek['pengeluaran'], 2) }}</td>
                <td>{{ number_format($rek['saldo_akhir'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="section-title">Hasil Pemeriksaan Rekening BLU:</p>
    <p>1. Saldo Akhir Rekening BLU : Rp {{ number_format($saldoAkhirBLU, 2) }}</p>
    <p>2. Pengesahan Pendapatan : Rp {{ number_format($pengesahanPendapatan, 2) }}</p>
    <p>3. Pengesahan Belanja : Rp {{ number_format($pengesahanBelanja, 2) }}</p>
    <p>4. Belum Pengesahan : Rp {{ number_format($belumPengesahan, 2) }}</p>
    <ul>
        <li>a) Pendapatan : Rp {{ number_format($belumPengesahanPendapatan, 2) }}</li>
        <li>b) Belanja : Rp {{ number_format($belumPengesahanBelanja, 2) }}</li>
    </ul>

    <p class="notes">Catatan :</p>

    {{-- Tombol Download hanya tampil jika bukan dalam mode download PDF --}}
@if(!isset($isDownload) || !$isDownload)
    <div class="download-button">
        @if(auth()->user()->hasRole('direktur'))
            <form action="{{ route('direktur.download-pdf') }}" method="POST">
                @csrf
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <button type="submit">Download PDF (Direktur)</button>
            </form>
        @endif

        @if(auth()->user()->hasRole('katim'))
        <form action="{{ route('katim.download-pdf') }}" method="POST" class="inline">
    @csrf
    <input type="hidden" name="bulan" value="{{ $bulan }}">
    <input type="hidden" name="tahun" value="{{ $tahun }}">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
        Download PDF
    </button>
</form>
        @endif
    </div>
@endif

</body>
</html>
