<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Direktur</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .spinner-border {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #ffffff;
            border-radius: 50%;
            width: 3rem;
            height: 3rem;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <!-- Loading Spinner -->
    <div id="loadingOverlay" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50">
        <div class="spinner-border"></div>
    </div>
  <!-- Header -->
  <nav class="bg-blue-600 p-4 shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-white text-2xl font-bold">Dashboard Direktur</h1>
        <div class="relative">
            <!-- Profile Dropdown Trigger -->
            <button id="profileButton" class="flex items-center space-x-1 text-white focus:outline-none">
                <img src="{{ asset('images/photowhite.png') }}" alt="Profile Picture" class="w-7 h-7">
                <span>{{ Auth::user()->name }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                <div class="px-4 py-3">
                    <p class="text-sm text-gray-600">Logged in as:</p>
                    <p class="text-sm font-medium text-gray-900">{{ Auth::user()->email }}</p>
                </div>
                <div class="border-t border-gray-200"></div>
                <ul class="p-4">
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-white bg-red-500 rounded-lg">
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

    <!-- Main Content -->
    <div class="container mx-auto mt-8">
        <!-- Welcome Section -->
<div class="bg-white shadow-md rounded-lg p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Selamat Datang, Direktur!</h2>
    <p class="text-gray-700 mb-6">Ini adalah halaman dashboard untuk Direktur. Pilih bulan dan tahun untuk melihat laporan rekonsiliasi saldo BLU.</p>

    <!-- Form Pilih Bulan dan Tahun -->
    <form action="{{ route('direktur.rekonsiliasi') }}" method="POST" target="_blank" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="bulan" class="block text-sm font-medium text-gray-700">Pilih Bulan:</label>
                <select name="bulan" id="bulan" required class="block w-full mt-1 p-2 border border-gray-300 rounded-lg">
                    <option value="" disabled selected>Pilih Bulan</option>
                    @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bln)
                        <option value="{{ $bln }}">{{ $bln }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tahun" class="block text-sm font-medium text-gray-700">Pilih Tahun:</label>
                <select name="tahun" id="tahun" required class="block w-full mt-1 p-2 border border-gray-300 rounded-lg">
                    @php
                        $currentYear = date('Y');
                        $years = range($currentYear - 2, $currentYear + 2);
                    @endphp
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            Lihat Rekonsiliasi
        </button>
    </form>
</div>

        <!-- Laporan Total dengan Selisih -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <!-- Filter Bulan dan Tahun -->
    <div class="bg-white p-4 rounded-lg shadow-md col-span-1">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="filterBulan" class="block text-sm font-medium text-gray-700">Bulan:</label>
                <select id="filterBulan" class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    <option value="">Semua Bulan</option>
                    @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                        <option value="{{ $bulan }}" {{ $bulan === $bulan ? 'selected' : '' }}>{{ $bulan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filterTahun" class="block text-sm font-medium text-gray-700">Tahun:</label>
                <select id="filterTahun" class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    @php
                        $currentYear = date('Y');
                        $years = range($currentYear - 1, $currentYear + 5);
                    @endphp
                    <option value="">Semua Tahun</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

         <!-- Summary Box -->
    <div class="bg-white p-4 rounded-lg shadow-md col-span-2">
        <h3 class="text-center font-bold mb-4 text-gray-700 border-b pb-2">Total</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-blue-50 p-3 rounded-lg text-center border border-blue-200">
                <p class="text-blue-800 font-semibold">Penerimaan</p>
                <p class="text-2xl mt-2 font-bold text-blue-600">Rp <span id="totalPenerimaan">{{ number_format($grandTotalPenerimaan, 0, ',', '.') }}</span></p>
            </div>
            <div class="bg-red-50 p-3 rounded-lg text-center border border-red-200">
                <p class="text-red-800 font-semibold">Pengeluaran</p>
                <p class="text-2xl mt-2 font-bold text-red-600">Rp <span id="totalPengeluaran">{{ number_format($grandTotalPengeluaran, 0, ',', '.') }}</span></p>
            </div>
            <div class="col-span-2 bg-gray-50 p-3 rounded-lg text-center border border-gray-200">
                <p class="text-gray-800 font-semibold">Selisih</p>
                <p class="text-2xl mt-2 font-bold {{ $grandTotalPenerimaan - $grandTotalPengeluaran >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    Rp <span id="totalSelisih">{{ number_format($grandTotalPenerimaan - $grandTotalPengeluaran, 0, ',', '.') }}</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Rincian Transaksi Perbulan -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-xl font-bold mb-4">Rincian Transaksi Perbulan</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-2 px-4 border">Tanggal</th>
                    <th class="py-2 px-4 border">Rekening</th>
                    <th class="py-2 px-4 border">Keterangan</th>
                    <th class="py-2 px-4 border">Debit (Masuk)</th>
                    <th class="py-2 px-4 border">Kredit (Keluar)</th>
                    <th class="py-2 px-4 border">Saldo</th>
                </tr>
            </thead>
            <tbody id="transaksiTableBody">
                <!-- Data akan diisi melalui AJAX -->
            </tbody>
        </table>
    </div>
</div>
</body>

<!-- Script setelah jQuery dimuat -->
<script>
    $(document).ready(function() {
    // Fungsi untuk format angka dengan pemisah ribuan
    function formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }
    
    // Fungsi untuk memuat rincian transaksi
    function loadTransaksiDetail() {
        const bulan = $('#filterBulan').val();
        const tahun = $('#filterTahun').val();
        
        // Tampilkan loading
        $('#transaksiTableBody').html('<tr><td colspan="6" class="text-center py-4">Memuat data...</td></tr>');
        
        $.ajax({
            url: "{{ route('direktur.getTransaksiDetail') }}",
            method: "GET",
            data: { bulan: bulan, tahun: tahun },
            success: function(response) {
                $('#transaksiTableBody').empty();
                
                // Update totals
                $('#totalPenerimaan').text(formatNumber(response.totalPenerimaan));
                $('#totalPengeluaran').text(formatNumber(response.totalPengeluaran));
                $('#totalSelisih').text(formatNumber(response.totalPenerimaan - response.totalPengeluaran));
                
                // Ubah warna selisih berdasarkan nilai
                const selisihElement = $('#totalSelisih').parent();
                if (response.totalPenerimaan - response.totalPengeluaran >= 0) {
                    selisihElement.removeClass('text-red-600').addClass('text-green-600');
                } else {
                    selisihElement.removeClass('text-green-600').addClass('text-red-600');
                }
                
                // Jika tidak ada data
                if (response.transaksi.length === 0) {
                    $('#transaksiTableBody').html('<tr><td colspan="6" class="text-center py-4">Tidak ada data untuk periode yang dipilih</td></tr>');
                    return;
                }
                
                // Inisialisasi saldo awal
                let currentSaldo = parseFloat(response.saldoAwal) || 0;
                
                // Tampilkan saldo awal
                $('#transaksiTableBody').append(`
                    <tr class="bg-gray-100">
                        <td class="py-2 px-4 border">${response.transaksi[0].tanggal_format || response.transaksi[0].tanggal}</td>
                        <td class="py-2 px-4 border">Semua Rekening</td>
                        <td class="py-2 px-4 border font-semibold">Saldo Awal</td>
                        <td class="py-2 px-4 border"></td>
                        <td class="py-2 px-4 border"></td>
                        <td class="py-2 px-4 border font-semibold">Rp ${formatNumber(currentSaldo)}</td>
                    </tr>
                `);
                
                // Variabel untuk melacak transaksi per tanggal yang sama
                let currentDate = '';
                let runningTotal = currentSaldo;
                
                // Tampilkan semua transaksi dengan saldo kumulatif
                response.transaksi.forEach(function(item, index) {
                    // Pastikan jumlah adalah angka
                    const jumlah = parseFloat(item.jumlah) || 0;
                    
                    // Update saldo running berdasarkan jenis transaksi
                    if (item.jenis === 'penerimaan') {
                        runningTotal += jumlah;
                    } else {
                        runningTotal -= jumlah;
                    }
                    
                    // Format tanggal untuk tampilan
                    const displayDate = item.tanggal_format || item.tanggal;
                    
                    $('#transaksiTableBody').append(`
                        <tr>
                            <td class="py-2 px-4 border">${displayDate}</td>
                            <td class="py-2 px-4 border">${item.rekening || '-'}</td>
                            <td class="py-2 px-4 border">${item.keterangan}</td>
                            <td class="py-2 px-4 border text-right ${item.jenis === 'penerimaan' ? 'text-blue-600 font-semibold' : ''}">
                                ${item.jenis === 'penerimaan' ? 'Rp ' + formatNumber(jumlah) : ''}
                            </td>
                            <td class="py-2 px-4 border text-right ${item.jenis === 'pengeluaran' ? 'text-red-600 font-semibold' : ''}">
                                ${item.jenis === 'pengeluaran' ? 'Rp ' + formatNumber(jumlah) : ''}
                            </td>
                            <td class="py-2 px-4 border text-right">Rp ${formatNumber(runningTotal)}</td>
                        </tr>
                    `);
                    
                    // Update tanggal saat ini
                    currentDate = displayDate;
                });
            },
            error: function(xhr, status, error) {
                console.error('Error:', xhr.responseText);
                $('#transaksiTableBody').html('<tr><td colspan="6" class="text-center py-4 text-red-600">Gagal memuat data: ' + error + '</td></tr>');
            }
        });
    }
    
    // Event listener untuk filter
    $('#filterBulan, #filterTahun').change(function() {
        loadTransaksiDetail();
    });
    
    // Load data saat halaman dimuat
    loadTransaksiDetail();
});
    </script>


<script>
        function showLoader() {
            document.getElementById('loadingOverlay').classList.remove('hidden');
        }
        $(document).ready(function() {
            const profileButton = document.getElementById('profileButton');
            const profileDropdown = document.getElementById('profileDropdown');

            // Toggle dropdown visibility
            profileButton.addEventListener('click', function (event) {
                event.stopPropagation();
                profileDropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function () {
                if (!profileDropdown.classList.contains('hidden')) {
                    profileDropdown.classList.add('hidden');
                }
            });
        });
    </script>
    </body>

</html>
