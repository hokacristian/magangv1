<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Direktur</title>
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
            <p class="text-gray-700 mb-6">Ini adalah halaman dashboard untuk Direktur. Pilih bulan untuk melihat laporan rekonsiliasi saldo BLU.</p>

            <!-- Form Pilih Bulan -->
            <form action="{{ route('direktur.rekonsiliasi') }}" method="POST" target="_blank" class="space-y-4">
                @csrf
                <div>
                    <label for="bulan" class="block text-sm font-medium text-gray-700">Pilih Bulan:</label>
                    <select name="bulan" id="bulan" required class="block w-full md:w-1/3 mt-1 p-2 border border-gray-300 rounded-lg">
                        <option value="" disabled selected>Pilih Bulan</option>
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bln)
                            <option value="{{ $bln }}">{{ $bln }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Lihat Rekonsiliasi
                </button>
            </form>
        </div>

        <!-- Filter Bulan -->
        <div class="mb-6">
            <form method="GET" action="{{ route('direktur.dashboard') }}" onsubmit="showLoader()" class="flex flex-col md:flex-row items-start md:items-center space-y-4 md:space-y-0 md:space-x-4">
                <div >
                    <label for="bulan" class="block text-sm font-medium text-gray-700">Pilih Bulan:</label>
                    <select name="bulan" id="bulan" class="border border-gray-300 rounded-lg p-2 mt-1">
                        <option value="">Semua Bulan</option>
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bln)
                            <option value="{{ $bln }}" {{ $bulan === $bln ? 'selected' : '' }}>{{ $bln }}</option>
                        @endforeach
                    </select>
                    <button type="submit"  class="bg-blue-600 hover:bg-blue-700 mx-2 text-white px-6 py-2 rounded-lg">Filter</button>
                </div>
            </form>
        </div>

        <!-- Laporan Total -->
        <h1 class="text-2xl font-bold mb-4">Laporan Total Penerimaan dan Pengeluaran</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Total Penerimaan -->
            <div class="bg-blue-100 p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Total Penerimaan</h2>
                <p class="text-3xl font-bold text-blue-600 mt-4">Rp {{ number_format($grandTotalPenerimaan, 2) }}</p>
                <table class="mt-4 w-full bg-white border border-gray-200 rounded-lg">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-2 px-4">Rekening</th>
                            <th class="py-2 px-4">Status</th>
                            <th class="py-2 px-4">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($totalPenerimaan as $item)
                        <tr class="border-t">
                            <td class="py-2 px-4">{{ $item->rekening->rekening }} - {{ $item->rekening->bank }}</td>
                            <td class="py-2 px-4">{{ $item->status }}</td>
                            <td class="py-2 px-4">{{ number_format($item->total_penerimaan, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Total Pengeluaran -->
            <div class="bg-red-100 p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Total Pengeluaran</h2>
                <p class="text-3xl font-bold text-red-600 mt-4">Rp {{ number_format($grandTotalPengeluaran, 2) }}</p>
                <table class="mt-4 w-full bg-white border border-gray-200 rounded-lg">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-2 px-4">Rekening</th>
                            <th class="py-2 px-4">Status</th>
                            <th class="py-2 px-4">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($totalPengeluaran as $item)
                        <tr class="border-t">
                            <td class="py-2 px-4">{{ $item->rekening->rekening }} - {{ $item->rekening->bank }}</td>
                            <td class="py-2 px-4">{{ $item->status }}</td>
                            <td class="py-2 px-4">{{ number_format($item->total_pengeluaran, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
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
</html>
