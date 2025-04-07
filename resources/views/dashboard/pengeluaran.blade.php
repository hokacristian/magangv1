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
                <div class="grid grid-cols-2 gap-4">
    <div>
        <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal:</label>
        <input type="date" name="tanggal" id="tanggal" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg" value="{{ date('Y-m-d') }}">
    </div>

    <div>
        <label for="bulan" class="block text-sm font-medium text-gray-700">Bulan:</label>
        <select name="bulan" id="bulan" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
            @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $bulan)
                <option value="{{ $bulan }}" {{ date('n') == $index + 1 ? 'selected' : '' }}>{{ $bulan }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tahun:</label>
        <div id="tahun_display" class="mt-1 p-2 bg-gray-100 border border-gray-300 rounded-lg">{{ date('Y') }}</div>
        <input type="hidden" name="tahun" id="tahun_input" value="{{ date('Y') }}">
    </div>
</div>
                    <div>
                        <label for="jumlah_pengeluaran" class="block text-sm font-medium text-gray-700">Jumlah Pengeluaran:</label>
                        <input type="number" name="jumlah_pengeluaran" step="0.01" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    </div>

                    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Event listener untuk update bulan dan tahun berdasarkan tanggal yang dipilih
    const tanggalInput = document.getElementById('tanggal');
    const bulanSelect = document.getElementById('bulan');
    const tahunDisplay = document.getElementById('tahun_display');
    const tahunInput = document.getElementById('tahun_input');
    
    if (tanggalInput && bulanSelect && tahunDisplay && tahunInput) {
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
    }
});
</script>

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
    <select id="keteranganSelect" class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
        <option value="" disabled selected>Pilih Keterangan</option>
        <option value="5100">5100</option>
        <option value="5101">5101</option>
        <option value="5102">5102</option>
        <option value="5103">5103</option>
        <option value="5104">5104</option>
        <option value="5105">5105</option>
        <option value="5200">5200</option>
        <option value="5201">5201</option>
        <option value="5202">5202</option>
        <option value="5203">5203</option>
        <option value="5204">5204</option>
        <option value="5205">5205</option>
        <option value="5300">5300</option>
        <option value="5301">5301</option>
        <option value="5302">5302</option>
        <option value="5303">5303</option>
        <option value="5304">5304</option>
        <option value="5305">5305</option>
        <option value="5400">5400</option>
        <option value="5401">5401</option>
        <option value="5402">5402</option>
        <option value="5403">5403</option>
        <option value="5404">5404</option>
        <option value="5405">5405</option>
        <option value="5500">5500</option>
        <option value="5501">5501</option>
        <option value="5502">5502</option>
        <option value="5503">5503</option>
        <option value="5504">5504</option>
        <option value="5505">5505</option>
    </select>
    <!-- Input tersembunyi yang akan menyimpan nilai kode COA -->
    <input type="hidden" name="keterangan" id="keteranganInput" required>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const keteranganSelect = document.getElementById('keteranganSelect');
    const keteranganInput = document.getElementById('keteranganInput');
    
    // Set nilai default untuk menghindari error validasi
    keteranganInput.value = '';
    
    keteranganSelect.addEventListener('change', function() {
        // Simpan HANYA kode COA (nilai value dari option)
        keteranganInput.value = this.value;
    });
});
</script>
<!-- Status tersembunyi, dengan nilai default "Sudah Disahkan" -->
<input type="hidden" name="status" value="Sudah Disahkan">

<!-- <div class="mt-1">
    <label class="block text-sm font-medium text-gray-700">Status:</label>
    <div class="mt-1 p-2 bg-green-100 text-green-800 border border-green-300 rounded-lg">
        Sudah Disahkan
    </div>
</div> -->

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

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Ambil elemen filter dan tabel
    const filterBulan = document.getElementById('filterBulanRiwayat');
    const filterTahun = document.getElementById('filterTahunRiwayat');
    const tableRows = document.querySelectorAll('#pengeluaranTable tbody tr');

    // Fungsi untuk menerapkan filter
    function applyFilter() {
        const selectedBulan = filterBulan.value;
        const selectedTahun = filterTahun.value;

        tableRows.forEach(row => {
            const rowBulan = row.getAttribute('data-bulan');
            const rowTahun = row.getAttribute('data-tahun');
            
            // Logika filter: tampilkan baris jika sesuai dengan filter atau filter kosong
            const showByBulan = selectedBulan === '' || rowBulan === selectedBulan;
            const showByTahun = selectedTahun === '' || rowTahun === selectedTahun;

            // Tampilkan atau sembunyikan baris berdasarkan hasil filter
            if (showByBulan && showByTahun) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Tambahkan event listener untuk perubahan filter
    filterBulan.addEventListener('change', applyFilter);
    filterTahun.addEventListener('change', applyFilter);

    // Jalankan filter saat halaman dimuat
    applyFilter();
});
</script>



<!-- Riwayat Pengeluaran -->
<div class="bg-white shadow-md rounded-lg p-6 mb-8">
    <h2 class="text-xl font-bold mb-4">Riwayat Pengeluaran</h2>
    <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden" id="pengeluaranTable">
        <thead class="bg-gray-200 text-gray-600">
            <tr>
                <th class="py-2 px-4">Tanggal</th>
                <th class="py-2 px-4">Bulan</th>
                <th class="py-2 px-4">Tahun</th>
                <th class="py-2 px-4">Rekening</th>
                <th class="py-2 px-4">Saldo Awal</th>
                <th class="py-2 px-4">Jumlah Pengeluaran</th>
                <th class="py-2 px-4">Saldo Akhir</th>
                <th class="py-2 px-4">COA</th>
                <th class="py-2 px-4">Keterangan</th>
                <th class="py-2 px-4">Status</th>
                <th class="py-2 px-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengeluarans as $pengeluaran)
                <tr class="border-t border-gray-200" 
                    data-bulan="{{ $pengeluaran->bulan }}" 
                    data-tahun="{{ $pengeluaran->tahun }}">
                    <td class="py-2 px-4">
                        {{ $pengeluaran->tanggal ? date('d-m-Y', strtotime($pengeluaran->tanggal)) : '-' }}
                    </td>
                    <td class="py-2 px-4">{{ $pengeluaran->bulan }}</td>
                    <td class="py-2 px-4">{{ $pengeluaran->tahun }}</td>
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
                    <td class="py-2 px-4 text-center coa-cell">{{ $pengeluaran->keterangan }}</td>
                    <td class="py-2 px-4 keterangan-cell" data-coa="{{ $pengeluaran->keterangan }}"></td>
                    <td class="py-2 px-4">
                        <div class="rounded p-1 bg-green-500 text-white text-center">
                            {{ $pengeluaran->status }}
                        </div>
                    </td>
                    <td class="py-2 px-4 flex space-x-2">
                        <button onclick="openEditModal('{{ $pengeluaran->id }}')" 
                                class="bg-blue-500 hover:bg-blue-700 text-white px-2 py-1 rounded">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button onclick="confirmDelete('{{ $pengeluaran->id }}')"
                                class="bg-red-500 hover:bg-red-700 text-white px-2 py-1 rounded">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

<!-- Modal Edit -->
<div id="editModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-bold mb-4">Edit Data Pengeluaran</h3>
        <form id="editForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_tanggal" class="block text-sm font-medium text-gray-700">Tanggal:</label>
                    <input type="date" name="tanggal" id="edit_tanggal" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                </div>
                
                <div>
                    <label for="edit_bulan" class="block text-sm font-medium text-gray-700">Bulan:</label>
                    <select name="bulan" id="edit_bulan" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                            <option value="{{ $bulan }}">{{ $bulan }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="edit_tahun" class="block text-sm font-medium text-gray-700">Tahun:</label>
                    <input type="number" name="tahun" id="edit_tahun" required min="2000" max="2100" class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                </div>
                
                <div>
                    <label for="edit_keterangan" class="block text-sm font-medium text-gray-700">COA:</label>
                    <select name="keterangan" id="edit_keterangan" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                        <option value="" disabled>Pilih COA</option>
                        @foreach(['5100', '5101', '5102', '5103', '5104', '5105', '5200', '5201', '5202', '5203', '5204', '5205', '5300', '5301', '5302', '5303', '5304', '5305', '5400', '5401', '5402', '5403', '5404', '5405', '5500', '5501', '5502', '5503', '5504', '5505'] as $coa)
                            <option value="{{ $coa }}">{{ $coa }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="edit_jumlah_pengeluaran" class="block text-sm font-medium text-gray-700">Jumlah Pengeluaran:</label>
                    <input type="number" name="jumlah_pengeluaran" id="edit_jumlah_pengeluaran" required step="0.01" min="0" class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                </div>
                
                <!-- Hidden status field - always set to "Sudah Disahkan" -->
                <input type="hidden" name="status" value="Sudah Disahkan">
            </div>
            
            <div class="flex justify-end space-x-2 mt-4">
                <button type="button" onclick="closeEditModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                    Batal
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Konfirmasi Hapus</h3>
        <p class="mb-4">Apakah Anda yakin ingin menghapus data ini?</p>
        
        <form id="deleteForm" action="" method="POST">
            @csrf
            @method('DELETE')
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeDeleteModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                    Tidak
                </button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                    Ya
                </button>
            </div>
        </form>
    </div>
</div>

</div>


<script>
    // Fungsi untuk membuka modal edit
    function openEditModal(id) {
        // Ambil data pengeluaran via AJAX
        $.ajax({
            url: `/pengeluaran/${id}/edit`,
            type: 'GET',
            success: function(data) {
                // Format tanggal dari database (YYYY-MM-DD) ke format input date
                const tanggal = data.tanggal ? data.tanggal.split('T')[0] : '';
                
                // Isi formulir dengan data yang ada
                $('#edit_tanggal').val(tanggal);
                $('#edit_bulan').val(data.bulan);
                $('#edit_tahun').val(data.tahun);
                $('#edit_keterangan').val(data.keterangan);
                $('#edit_jumlah_pengeluaran').val(data.jumlah_pengeluaran);
                
                // Set action form ke route update
                $('#editForm').attr('action', `/pengeluaran/${id}`);
                
                // Tampilkan modal
                $('#editModal').removeClass('hidden').addClass('flex');
                
                // Initialize formatted input after modal is shown
                initializeFormattedEditInput();
                
                // If there's an existing formatted input, set its value
                const formattedEditInput = document.getElementById('formatted_edit_jumlah_pengeluaran');
                if (formattedEditInput && data.jumlah_pengeluaran) {
                    formattedEditInput.value = formatEditRupiah(data.jumlah_pengeluaran);
                }
            },
            error: function() {
                alert('Gagal mengambil data pengeluaran!');
            }
        });
    }
    
    // Fungsi untuk menutup modal edit
    function closeEditModal() {
        $('#editModal').removeClass('flex').addClass('hidden');
    }
    
    // Fungsi untuk konfirmasi hapus
    function confirmDelete(id) {
        // Set action form ke route destroy
        $('#deleteForm').attr('action', `/pengeluaran/${id}`);
        
        // Tampilkan modal konfirmasi
        $('#deleteModal').removeClass('hidden').addClass('flex');
    }
    
    // Fungsi untuk menutup modal konfirmasi hapus
    function closeDeleteModal() {
        $('#deleteModal').removeClass('flex').addClass('hidden');
    }
    
    // Format number dengan thousand separator (titik)
    function formatEditRupiah(angka) {
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
    
    // Function to initialize formatted input in edit modal
    function initializeFormattedEditInput() {
        // Get original input
        const editInputPengeluaran = document.getElementById('edit_jumlah_pengeluaran');
        
        if (!editInputPengeluaran) return; // Exit if element not found
        
        // Check if formatted input already exists
        if (document.getElementById('formatted_edit_jumlah_pengeluaran')) return;
        
        // Create additional input element for formatted display
        const formattedEditInput = document.createElement('input');
        formattedEditInput.type = 'text';
        formattedEditInput.className = editInputPengeluaran.className;
        formattedEditInput.placeholder = editInputPengeluaran.placeholder || 'Masukkan jumlah';
        formattedEditInput.id = 'formatted_edit_jumlah_pengeluaran';
        
        // Hide original input
        editInputPengeluaran.style.display = 'none';
        
        // Insert formatted input after original input
        editInputPengeluaran.parentNode.insertBefore(formattedEditInput, editInputPengeluaran.nextSibling);
        
        // Event listener when user types in formatted input
        formattedEditInput.addEventListener('input', function(e) {
            // Save cursor position
            const cursorPos = this.selectionStart;
            
            // Get value without format (to be saved in original input)
            const value = this.value.replace(/\./g, '');
            
            // Save unformatted value to original input
            editInputPengeluaran.value = value;
            
            // Format value for display
            const formattedValue = formatEditRupiah(value);
            
            // Calculate length change before and after formatting
            const lengthDiff = formattedValue.length - this.value.length;
            
            // Update displayed value
            this.value = formattedValue;
            
            // Reset cursor position considering length change
            this.setSelectionRange(cursorPos + lengthDiff, cursorPos + lengthDiff);
        });

        // Tambahkan event listener untuk update bulan dan tahun saat tanggal berubah
    const editTanggalInput = document.getElementById('edit_tanggal');
    const editBulanSelect = document.getElementById('edit_bulan');
    const editTahunInput = document.getElementById('edit_tahun');
    
    if (editTanggalInput && editBulanSelect && editTahunInput) {
        editTanggalInput.addEventListener('change', function() {
            const tanggal = new Date(this.value);
            const bulanIndex = tanggal.getMonth(); // 0-11
            const tahun = tanggal.getFullYear();
            
            // Update bulan dropdown berdasarkan tanggal
            editBulanSelect.selectedIndex = bulanIndex;
            
            // Update input tahun
            editTahunInput.value = tahun;
        });
    }
}



</script>

<script>
// Kamus kode COA untuk pengeluaran
const coaDictionary = {
    "5100": "Beban Pegawai & Tenaga Pendidik",
    "5101": "Gaji Dosen & Tunjangan Sertifikasi",
    "5102": "Gaji Tenaga Kependidikan",
    "5103": "Honorarium Dosen Luar Biasa",
    "5104": "BPJS Kesehatan & Ketenagakerjaan",
    "5105": "Biaya Pelatihan dan Pengembangan SDM",
    "5200": "Beban Operasional Pendidikan",
    "5201": "Pembelian Alat Tulis Kantor (ATK)",
    "5202": "Biaya Listrik, Air, dan Internet",
    "5203": "Pemeliharaan Gedung dan Peralatan",
    "5204": "Biaya Transportasi dan Perjalanan Dinas",
    "5205": "Biaya Konsumsi dan Rapat",
    "5300": "Beban Akademik & Penelitian",
    "5301": "Biaya Praktikum Mahasiswa",
    "5302": "Biaya Pengadaan Bahan Lab & Simulasi",
    "5303": "Biaya Penelitian Dosen dan Mahasiswa",
    "5304": "Biaya Publikasi Ilmiah dan Seminar",
    "5305": "Biaya Akreditasi & Sertifikasi Program Studi",
    "5400": "Beban Mahasiswa & Kegiatan Kemahasiswaan",
    "5401": "Biaya Organisasi Mahasiswa (BEM, HIMA)",
    "5402": "Biaya Kegiatan UKM & Kesejahteraan Mahasiswa",
    "5403": "Bantuan & Beasiswa Mahasiswa",
    "5404": "Biaya Kegiatan Wisuda",
    "5405": "Bantuan Kesehatan dan Sosial Mahasiswa",
    "5500": "Beban Lain-lain",
    "5501": "Biaya Penyusutan Aset",
    "5502": "Pajak & Retribusi",
    "5503": "Biaya Pengelolaan Sampah dan Limbah Medis",
    "5504": "Biaya CSR & Kegiatan Sosial",
    "5505": "Biaya Lain-lain Tak Terduga"
};

// Isi kolom keterangan berdasarkan kode COA
document.addEventListener('DOMContentLoaded', function() {
    const cells = document.querySelectorAll('.keterangan-cell');
    cells.forEach(cell => {
        const coa = cell.getAttribute('data-coa');
        if (coaDictionary[coa]) {
            cell.textContent = coaDictionary[coa];
        } else {
            cell.textContent = "Keterangan tidak ditemukan";
        }
    });
    
    // Event listener untuk filter bulan
    const filterBulan = document.getElementById('filterBulan');
    const tableRows = document.querySelectorAll('#pengeluaranTable tbody tr');

    filterBulan.addEventListener('change', function() {
        const selectedBulan = this.value;
        
        tableRows.forEach(row => {
            const rowBulan = row.getAttribute('data-bulan');
            
            if (selectedBulan === '' || rowBulan === selectedBulan) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Event listener untuk update tanggal dan tahun
    const tanggalInput = document.getElementById('tanggal');
    const tahunDisplay = document.getElementById('tahun_display');
    const tahunInput = document.getElementById('tahun_input');
    
    tanggalInput.addEventListener('change', function() {
        const tanggal = new Date(this.value);
        const tahun = tanggal.getFullYear();
        
        // Update tahun display dan hidden input
        tahunDisplay.textContent = tahun;
        tahunInput.value = tahun;
    });
});
</script>

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




</body>
</html>
