<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penerimaan</title>
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
        <h1 class="text-white text-2xl font-bold">Dashboard Penerimaan</h1>
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

        <!-- Input Data Penerimaan -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
    <h2 class="text-xl font-bold mb-4">Input Data Penerimaan</h2>
    <form action="{{ route('penerimaan.store') }}" method="POST" class="space-y-4" onsubmit="showLoader()">
        @csrf

        <div>
    <label for="rekening_id" class="block text-sm font-medium text-gray-700">Pilih Rekening:</label>
    <select name="rekening_id" id="rekening_id" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
        <option value="" disabled selected>Pilih Rekening</option>
        @foreach($rekenings as $rekening)
            <option value="{{ $rekening->id }}" data-saldo="{{ $rekening->saldo }}">
                {{ $rekening->rekening }} - {{ $rekening->bank }}
            </option>
        @endforeach
    </select>
</div>
<p class="text-lg text-gray-600">
    <strong>Saldo Saat Ini:</strong>
    <span id="saldo_saat_ini" class="text-blue-500">RP. 0</span>
</p>

<script>
    function formatRupiah(angka) {
        return 'RP. ' + angka.toLocaleString('id-ID');
    }

    const selectRekening = document.getElementById('rekening_id');
    
    function updateSaldo() {
        const selectedOption = selectRekening.options[selectRekening.selectedIndex];
        const saldo = parseFloat(selectedOption.getAttribute('data-saldo')) || 0;
        document.getElementById('saldo_saat_ini').textContent = formatRupiah(saldo);
    }

    // Update saldo saat pilihan berubah
    selectRekening.addEventListener('change', updateSaldo);
    
    // Inisialisasi saldo pertama kali
    updateSaldo();
</script>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="bulan" class="block text-sm font-medium text-gray-700">Bulan:</label>
                <select name="bulan" id="bulan" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $bulan)
                        <option value="{{ $bulan }}" {{ date('n') == $index + 1 ? 'selected' : '' }}>{{ $bulan }}</option>
                    @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="penerimaan" class="block text-sm font-medium text-gray-700">Jumlah Penerimaan:</label>
                        <input type="number" name="penerimaan" step="0.01" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal:</label>
                <input type="date" name="tanggal" id="tanggal" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg" value="{{ date('Y-m-d') }}">
            </div>

            <div>
            <label class="block text-sm font-medium text-gray-700">Tahun:</label>
            <div id="tahun_display" class="mt-1 p-2 bg-gray-100 border border-gray-300 rounded-lg">{{ date('Y') }}</div>
            <input type="hidden" name="tahun" id="tahun_input" value="{{ date('Y') }}">
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
    // Ambil elemen input penerimaan
    const inputPenerimaan = document.querySelector('input[name="penerimaan"]');
    
    // Buat elemen input tambahan untuk tampilan yang diformat
    const formattedInput = document.createElement('input');
    formattedInput.type = 'text';
    formattedInput.className = inputPenerimaan.className;
    formattedInput.placeholder = inputPenerimaan.placeholder || 'Masukkan jumlah';
    
    // Sembunyikan input asli
    inputPenerimaan.style.display = 'none';
    
    // Masukkan input yang diformat setelah input asli
    inputPenerimaan.parentNode.insertBefore(formattedInput, inputPenerimaan.nextSibling);
    
    // Event listener saat user mengetik di input yang diformat
    formattedInput.addEventListener('input', function(e) {
        // Simpan posisi kursor
        const cursorPos = this.selectionStart;
        
        // Dapatkan nilai tanpa format (untuk disimpan di input asli)
        const value = this.value.replace(/\./g, '');
        
        // Simpan nilai tanpa format ke input asli
        inputPenerimaan.value = value;
        
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
    if (inputPenerimaan.value) {
        formattedInput.value = formatRupiah(inputPenerimaan.value);
    }

    // Event listener untuk update bulan berdasarkan tanggal yang dipilih
    const tanggalInput = document.getElementById('tanggal');
    const bulanSelect = document.getElementById('bulan');
    const tahunDisplay = document.getElementById('tahun_display');
    const tahunInput = document.getElementById('tahun_input');
    
    tanggalInput.addEventListener('change', function() {
        const tanggal = new Date(this.value);
        const bulanIndex = tanggal.getMonth(); // 0-11
        const tahun = tanggal.getFullYear();
        
        // Update bulan dropdown sesuai tanggal
        bulanSelect.selectedIndex = bulanIndex;
        
        // Update tahun display dan hidden input
        tahunDisplay.textContent = tahun;
        tahunInput.value = tahun;
    });
});
</script>
                </div>

                <div class="grid grid-cols-2 gap-4">
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
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Simpan
                </button>
            </form>
        </div>
        <!-- Filter Bagian -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label for="filterBulanRiwayat" class="block text-sm font-medium text-gray-700">Filter Berdasarkan Bulan:</label>
        <select id="filterBulanRiwayat" class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            <option value="">Semua Bulan</option>
            @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                <option value="{{ $bulan }}">{{ $bulan }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="filterTahunRiwayat" class="block text-sm font-medium text-gray-700">Filter Berdasarkan Tahun:</label>
        <select id="filterTahunRiwayat" class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
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
        <!-- Riwayat Penerimaan -->
<div class="bg-white shadow-md rounded-lg p-6 mb-8">
    <h2 class="text-xl font-bold mb-4">Riwayat Penerimaan</h2>
    <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-200 text-gray-600">
            <tr>
                <!-- Tambahkan kolom Tanggal dan Tahun -->
                <th class="py-2 px-4">Tanggal</th>
                <th class="py-2 px-4">Bulan</th>
                <th class="py-2 px-4">Tahun</th>
                <th class="py-2 px-4">Rekening</th>
                <th class="py-2 px-4">Saldo Awal</th>
                <th class="py-2 px-4">Penerimaan</th>
                <th class="py-2 px-4">Saldo Akhir</th>
                <th class="py-2 px-4">Keterangan</th>
                <th class="py-2 px-4">Status</th>
            </tr>
        </thead>
        <tbody id="riwayatPenerimaanBody">
            @foreach($penerimaans as $penerimaan)
                <tr class="border-t border-gray-200" 
                    data-bulan="{{ $penerimaan->bulan }}" 
                    data-tahun="{{ $penerimaan->tahun }}">
                    <!-- Tambahkan tampilan tanggal dan tahun -->
                    <td class="py-2 px-4">
                        {{ $penerimaan->tanggal ? date('d-m-Y', strtotime($penerimaan->tanggal)) : '-' }}
                    </td>
                    <td class="py-2 px-4">{{ $penerimaan->bulan }}</td>
                    <td class="py-2 px-4">{{ $penerimaan->tahun }}</td>
                    <td class="py-2 px-4">{{ $penerimaan->rekening->rekening }} - {{ $penerimaan->rekening->bank }}</td>
                    <td class="py-2 px-4">{{ number_format($penerimaan->saldo_awal, 2) }}</td>
                    <td class="py-2 px-4">{{ number_format($penerimaan->penerimaan, 2) }}</td>
                    <td class="py-2 px-4">
                        @if ($penerimaan->status === 'Sudah Disahkan')
                            {{ number_format($penerimaan->saldo_akhir, 2) }}
                        @else
                            {{ number_format($penerimaan->saldo_awal, 2) }}
                        @endif
                    </td>
                    <td class="py-2 px-4">{{ $penerimaan->keterangan }}</td>
                    <td class="py-2 px-4">
                        <form action="{{ route('penerimaan.updateStatus', $penerimaan->id) }}" method="POST">
                            @csrf
                            <select 
                                name="status" 
                                onchange="this.form.submit()" 
                                class="rounded p-1 focus:outline-none focus:ring-2 
                                    @if ($penerimaan->status === 'Sudah Disahkan') 
                                        bg-green-500 text-white 
                                    @else 
                                        bg-red-500 text-white 
                                    @endif">
                                <option 
                                    value="Sudah Disahkan" 
                                    {{ $penerimaan->status === 'Sudah Disahkan' ? 'selected' : '' }}
                                    class="text-white bg-green-500">
                                    Sudah Disahkan
                                </option>
                                <option 
                                    value="Belum Disahkan" 
                                    {{ $penerimaan->status === 'Belum Disahkan' ? 'selected' : '' }}
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


            </table>
        </div>
                <!-- Belum Disahkan -->
                <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-xl font-bold mb-4 text-red-600">Belum Disahkan</h3>
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-200 text-gray-600">
                    <tr>
                        <th class="py-2 px-4">Bulan</th>
                        <th class="py-2 px-4">Rekening</th>
                        <th class="py-2 px-4">Saldo Awal</th>
                        <th class="py-2 px-4">Penerimaan</th>
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
                            <td class="py-2 px-4">{{ number_format($data->penerimaan, 2) }}</td>
                            <td class="py-2 px-4">{{ $data->keterangan }}</td>
                            <td class="py-2 px-4">{{ $data->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Total Penerimaan -->
<div class="mt-8">
    <div class="bg-blue-600 text-white p-6 rounded-lg shadow-md flex justify-between items-center">
        <div>
            <h3 class="text-lg font-bold">Total Penerimaan</h3>
            <p class="text-2xl font-semibold">Rp <span id="totalPendapatan">{{ number_format($totalPendapatan, 2) }}</span></p>
        </div>
    </div>
</div>

<!-- Filter untuk Total Penerimaan -->
<div class="mt-6 bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-bold mb-4">Filter Data Penerimaan</h3>
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

<!-- Tabel Total Penerimaan -->
<div id="filteredTable" class="mt-6 bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-bold mb-4">Data Penerimaan Berdasarkan Filter</h3>
    <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-200 text-gray-600">
            <tr>
                <th class="py-2 px-4 text-left border-b border-gray-300">Bulan</th>
                <th class="py-2 px-4 text-left border-b border-gray-300">Rekening</th>
                <th class="py-2 px-4 text-left border-b border-gray-300">Penerimaan</th>
            </tr>
        </thead>
        <tbody id="filteredTableBody" class="divide-y divide-gray-200">
            <!-- Konten akan diperbarui melalui AJAX -->
        </tbody>
    </table>
</div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Fungsi untuk memperbarui tabel total penerimaan
        function updateTotalPenerimaan() {
            let bulan = $('#bulanFilter').val();
            let status = $('#statusFilter').val();

            $.ajax({
                url: "{{ route('penerimaan.dashboard') }}",
                method: "GET",
                data: { bulan: bulan, status: status },
                success: function (response) {
                    $('#filteredTableBody').empty(); // Kosongkan tabel
                    $('#totalPendapatan').text(response.totalPendapatan.toLocaleString('id-ID')); // Update total

                    // Masukkan data baru ke tabel filteredTable
                    response.filteredData.forEach(function (item) {
                        $('#filteredTableBody').append(`
                            <tr>
                                <td>${item.bulan}</td>
                                <td>${item.rekening}</td>
                                <td>${item.penerimaan.toLocaleString('id-ID')}</td>
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
        $('#bulanFilter, #statusFilter').change(updateTotalPenerimaan);

        // Panggil fungsi pertama kali
        updateTotalPenerimaan();
    });
</script>



    <!-- SweetAlert2 Toast -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Flash message for success
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

            // Flash message for error
            @if (session('error'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: '{{ session('error') }}',
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
                    // Pastikan saldo adalah angka sebelum diformat
                    let saldo = Number(data.saldo_saat_ini) || 0;

                    // Format saldo dengan "Rp." di awalnya dan pemisah ribuan
                    $('#saldo_saat_ini').text('Rp. ' + saldo.toLocaleString('id-ID'));
                },
                error: function () {
                    alert('Gagal mendapatkan saldo rekening.');
                }
            });
        } else {
            $('#saldo_saat_ini').text('Rp. 0');
        }
    });
});


</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterBulanRiwayat = document.getElementById('filterBulanRiwayat');
        const filterTahunRiwayat = document.getElementById('filterTahunRiwayat');
        const riwayatRows = document.querySelectorAll('#riwayatPenerimaanBody tr');

        // Event Listener untuk filter bulan dan tahun
        function applyFilter() {
            const selectedBulan = filterBulanRiwayat.value;
            const selectedTahun = filterTahunRiwayat.value;

            riwayatRows.forEach(row => {
                const rowBulan = row.getAttribute('data-bulan');
                const rowTahun = row.getAttribute('data-tahun');
                const showByBulan = selectedBulan === '' || rowBulan === selectedBulan;
                const showByTahun = selectedTahun === '' || rowTahun === selectedTahun;

                if (showByBulan && showByTahun) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        filterBulanRiwayat.addEventListener('change', applyFilter);
        filterTahunRiwayat.addEventListener('change', applyFilter);

        // Initial filter application
        applyFilter();
    });
</script>

</body>
</html>
