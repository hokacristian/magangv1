<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengeluaran</title>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
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
            <h1 class="text-white text-2xl font-bold">Dashboard Pengeluaran</h1>
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

        <!-- Input Data Pengeluaran -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Input Data Pengeluaran</h2>
            <form action="{{ route('pengeluaran.store') }}" method="POST" class="space-y-4" onsubmit="showLoader()">
                @csrf

                <div>
                    <label for="rekening_id" class="block text-sm font-medium text-gray-700">Pilih Rekening:</label>
                    <select name="rekening_id" id="rekening_id" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                        <option value="" disabled selected>Pilih Rekening</option>
                        @foreach($rekenings as $rekening)
                            <option value="{{ $rekening->id }}">{{ $rekening->rekening }} - {{ $rekening->bank }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="text-sm text-gray-600"><strong>Saldo Saat Ini:</strong> <span id="saldo_saat_ini" class="text-blue-500">0</span></p>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="bulan" class="block text-sm font-medium text-gray-700">Bulan:</label>
                        <select name="bulan" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                            @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                                <option value="{{ $bulan }}">{{ $bulan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="jumlah_pengeluaran" class="block text-sm font-medium text-gray-700">Jumlah Pengeluaran:</label>
                        <input type="number" name="jumlah_pengeluaran" step="0.01" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    </div>

                    <script>
// Format number dengan thousand separator (titik)
function formatRupiah(angka) {
    // Pastikan input adalah string
    angka = String(angka);
    
    // Hapus semua karakter non-digit
    angka = angka.replace(/[^\d]/g, '');
    
    // Split angka untuk memisahkan bagian desimal jika ada
    const parts = angka.split('.');
    const numberPart = parts[0];
    const decimalPart = parts.length > 1 ? '.' + parts[1] : '';
    
    // Format dengan thousand separator (titik untuk format Indonesia)
    const formattedNumber = numberPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    
    return formattedNumber + decimalPart;
}

document.addEventListener('DOMContentLoaded', function() {
    // Ambil elemen input inputPengeluaran
    const inputPengeluaran = document.querySelector('input[name="jumlah_pengeluaran"]');
    
    // Buat elemen input tambahan untuk tampilan yang diformat
    const formattedInput = document.createElement('input');
    formattedInput.type = 'text';
    formattedInput.className = inputPengeluaran.className;
    formattedInput.placeholder = inputPengeluaran.placeholder || 'Masukkan jumlah';
    
    // Sembunyikan input asli
    inputPengeluaran.style.display = 'none';
    
    // Masukkan input yang diformat setelah input asli
    inputPengeluaran.parentNode.insertBefore(formattedInput, inputPengeluaran.nextSibling);
    
    // Event listener saat user mengetik di input yang diformat
    formattedInput.addEventListener('input', function(e) {
        // Simpan posisi kursor
        const cursorPos = this.selectionStart;
        
        // Dapatkan nilai tanpa format (untuk disimpan di input asli)
        const value = this.value.replace(/\./g, '');
        
        // Simpan nilai tanpa format ke input asli
        inputPengeluaran.value = value;
        
        // Format nilai untuk ditampilkan
        const formattedValue = formatRupiah(value);
        
        // Hitung perubahan panjang sebelum dan sesudah format
        const lengthDiff = formattedValue.length - this.value.length;
        
        // Perbarui nilai yang ditampilkan
        this.value = formattedValue;
        
        // Atur ulang posisi kursor dengan mempertimbangkan perubahan panjang
        this.setSelectionRange(cursorPos + lengthDiff, cursorPos + lengthDiff);
    });
    
    // Event listener untuk copy nilai dari input asli ke input yang diformat saat halaman dimuat
    if (inputPengeluaran.value) {
        formattedInput.value = formatRupiah(inputPengeluaran.value);
    }
});
</script>
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan:</label>
                    <input type="text" name="keterangan" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status:</label>
                    <select name="status" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                        <option value="Sudah Disahkan">Sudah Disahkan</option>
                        <option value="Belum Disahkan">Belum Disahkan</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Simpan
                </button>
            </form>
        </div>

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
    <div class="mb-4">
        <label for="filterBulan" class="block text-sm font-medium text-gray-700">Filter Berdasarkan Bulan:</label>
        <select id="filterBulan" class="mt-1 block w-full md:w-1/3 p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            <option value="">Semua Bulan</option>
            @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                <option value="{{ $bulan }}">{{ $bulan }}</option>
            @endforeach
        </select>
    </div>

        <!-- Riwayat Pengeluaran -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Riwayat Pengeluaran</h2>
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden" id="pengeluaranTable">
                <thead class="bg-gray-200 text-gray-600">
                    <tr>
                        <th class="py-2 px-4">Bulan</th>
                        <th class="py-2 px-4">Rekening</th>
                        <th class="py-2 px-4">Saldo Awal</th>
                        <th class="py-2 px-4">Jumlah Pengeluaran</th>
                        <th class="py-2 px-4">Saldo Akhir</th>
                        <th class="py-2 px-4">Keterangan</th>
                        <th class="py-2 px-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengeluarans as $pengeluaran)
                        <tr class="border-t border-gray-200" data-bulan="{{ $pengeluaran->bulan }}">
                            <td class="py-2 px-4">{{ $pengeluaran->bulan }}</td>
                            <td class="py-2 px-4">{{ $pengeluaran->rekening->rekening }} - {{ $pengeluaran->rekening->bank }}</td>
                            <td class="py-2 px-4">{{ number_format($pengeluaran->saldo_awal, 2) }}</td>
                            <td class="py-2 px-4">{{ number_format($pengeluaran->jumlah_pengeluaran, 2) }}</td>
                            <td class="py-2 px-4">
                                @if ($pengeluaran->status === 'Sudah Disahkan')
                                    {{ number_format($pengeluaran->saldo_akhir, 2) }}
                                @else
                                    {{ number_format($pengeluaran->saldo_awal, 2) }}
                                @endif
                            </td>
                            <td class="py-2 px-4">{{ $pengeluaran->keterangan }}</td>
                            <td class="py-2 px-4">
                            <form action="{{ route('pengeluaran.updateStatus', $pengeluaran->id) }}" method="POST">
                @csrf
                <select 
                    name="status" 
                    onchange="this.form.submit()" 
                    class="rounded p-1 focus:outline-none focus:ring-2 
                        @if ($pengeluaran->status === 'Sudah Disahkan') 
                            bg-green-500 text-white 
                        @else 
                            bg-red-500 text-white 
                        @endif">
                    <option 
                        value="Sudah Disahkan" 
                        {{ $pengeluaran->status === 'Sudah Disahkan' ? 'selected' : '' }}
                        class="text-white bg-green-500">
                        Sudah Disahkan
                    </option>
                    <option 
                        value="Belum Disahkan" 
                        {{ $pengeluaran->status === 'Belum Disahkan' ? 'selected' : '' }}
                        class="text-white bg-red-500">
                        Belum Disahkan
                    </option>
                    </select>
                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
<!-- BELUM DISAHKAN -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-4 text-red-600">Belum Disahkan</h2>
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-200 text-gray-600">
                    <tr>
                        <th class="py-2 px-4">Bulan</th>
                        <th class="py-2 px-4">Rekening</th>
                        <th class="py-2 px-4">Saldo Awal</th>
                        <th class="py-2 px-4">Jumlah Pengeluaran</th>
                        <th class="py-2 px-4">Keterangan</th>
                        <th class="py-2 px-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($belumDisahkan as $data)
                        <tr class="border-t border-gray-200">
                            <td class="py-2 px-4">{{ $data->bulan }}</td>
                            <td class="py-2 px-4">{{ $data->rekening->rekening }} - {{ $data->rekening->bank }}</td>
                            <td class="py-2 px-4">{{ number_format($data->saldo_awal, 2) }}</td>
                            <td class="py-2 px-4">{{ number_format($data->jumlah_pengeluaran, 2) }}</td>
                            <td class="py-2 px-4">{{ $data->keterangan }}</td>
                            <td class="py-2 px-4">{{ $data->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Total Pengeluaran -->
        <div class="mt-4">
            <div class="bg-blue-600 text-white p-6 rounded-lg shadow-md flex justify-between items-center">
                <h3 class="text-lg font-bold">Total Pengeluaran: Rp <span id="totalPengeluaran">{{ number_format($totalPengeluaran, 2) }}</span></h3>
            </div>
        </div>

        <!-- Filter untuk Total Pengeluaran -->
        <div class="mt-6 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-bold mb-4">Filter Data Pengeluaran</h3>
            <form id="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="bulanFilter" class="block text-sm font-medium text-gray-700">Bulan:</label>
                    <select name="bulan" id="bulanFilter" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="" disabled selected>Pilih Bulan</option>
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $month)
                            <option value="{{ $month }}">{{ $month }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700">Status:</label>
                    <select name="status" id="statusFilter" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="" disabled selected>Pilih Status</option>
                        <option value="Sudah Disahkan">Sudah Disahkan</option>
                        <option value="Belum Disahkan">Belum Disahkan</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Tabel Total Pengeluaran (dengan filter) -->
        <div id="filteredTable" class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-bold mb-4">Data Total Berdasarkan Filter</h3>
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-200">
                    <tr>
                        <th>Bulan</th>
                        <th>Rekening</th>
                        <th>Pengeluaran</th>
                    </tr>
                </thead>
                <tbody id="filteredTableBody">
                    <!-- Konten akan diperbarui melalui AJAX -->
                </tbody>
            </table>
        </div>
    </div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Fungsi untuk memperbarui tabel total pengeluaran
        function updateTotalPengeluaran() {
            let bulan = $('#bulanFilter').val();
            let status = $('#statusFilter').val();

            $.ajax({
                url: "{{ route('pengeluaran.dashboard') }}", // Pastikan URL ini mengarah ke route pengeluaran
                method: "GET",
                data: { bulan: bulan, status: status },
                success: function (response) {
                    $('#filteredTableBody').empty(); // Kosongkan tabel
                    $('#totalPengeluaran').text(response.totalPengeluaran.toLocaleString('id-ID')); // Update total

                    // Masukkan data baru ke tabel filteredTable
                    response.filteredData.forEach(function (item) {
                        $('#filteredTableBody').append(`
                            <tr>
                                <td>${item.bulan}</td>
                                <td>${item.rekening}</td>
                                <td>${item.jumlah_pengeluaran.toLocaleString('id-ID')}</td>
                            </tr>
                        `);
                    });
                },
                error: function () {
                    alert('Gagal memuat data!');
                }
            });
        }

        // Event listener untuk filter dropdown
        $('#bulanFilter, #statusFilter').change(updateTotalPengeluaran);

        // Panggil fungsi pertama kali
        updateTotalPengeluaran();
    });
</script>



    <!-- SweetAlert2 Toast -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const profileButton = document.getElementById('profileButton');
            const profileDropdown = document.getElementById('profileDropdown');

            profileButton.addEventListener('click', function (event) {
                event.stopPropagation();
                profileDropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', function () {
                if (!profileDropdown.classList.contains('hidden')) {
                    profileDropdown.classList.add('hidden');
                }
            });

            @if (session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 3000
                });
            @endif
        });
    </script>
<script>
    function showLoader() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }
    document.addEventListener('DOMContentLoaded', function () {
        const filterBulan = document.getElementById('filterBulan');
        const tableRows = document.querySelectorAll('#pengeluaranTable tbody tr');

        // Event listener untuk perubahan pada dropdown filter
        filterBulan.addEventListener('change', function () {
            const selectedBulan = filterBulan.value;

            tableRows.forEach(row => {
                // Ambil data bulan dari atribut data-bulan
                const rowBulan = row.getAttribute('data-bulan');

                // Tampilkan atau sembunyikan baris berdasarkan bulan
                if (selectedBulan === '' || rowBulan === selectedBulan) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>



</body>
</html>
