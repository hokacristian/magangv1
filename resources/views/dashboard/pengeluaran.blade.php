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
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <!-- Header -->
    <nav class="bg-blue-600 p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-white text-2xl font-bold">Dashboard Pengeluaran</h1>
            <div class="relative">
                <!-- Profile Dropdown Trigger -->
                <button id="profileButton" class="flex items-center space-x-2 text-white focus:outline-none">
                <img src="{{ asset('images/photowhite.png') }}" alt="Profile Picture" class="w-10 h-10">
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
            <form action="{{ route('pengeluaran.store') }}" method="POST" class="space-y-4">
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

        <!-- Riwayat Pengeluaran -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Riwayat Pengeluaran</h2>
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
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
                        <tr class="border-t border-gray-200">
                            <td class="py-2 px-4">{{ $pengeluaran->bulan }}</td>
                            <td class="py-2 px-4">{{ $pengeluaran->rekening->rekening }} - {{ $pengeluaran->rekening->bank }}</td>
                            <td class="py-2 px-4">{{ number_format($pengeluaran->saldo_awal, 2) }}</td>
                            <td class="py-2 px-4">{{ number_format($pengeluaran->jumlah_pengeluaran, 2) }}</td>
                            <td class="py-2 px-4">{{ number_format($pengeluaran->saldo_akhir, 2) }}</td>
                            <td class="py-2 px-4">{{ $pengeluaran->keterangan }}</td>
                            <td class="py-2 px-4">
                                <form action="{{ route('pengeluaran.updateStatus', $pengeluaran->id) }}" method="POST">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" class="bg-gray-100 rounded p-1">
                                        <option value="Sudah Disahkan" {{ $pengeluaran->status === 'Sudah Disahkan' ? 'selected' : '' }}>Sudah Disahkan</option>
                                        <option value="Belum Disahkan" {{ $pengeluaran->status === 'Belum Disahkan' ? 'selected' : '' }}>Belum Disahkan</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

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
