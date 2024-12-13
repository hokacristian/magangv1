<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengeluaran;

class PengeluaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pengeluarans = [
            [
                'rekening_id' => 1, // ID rekening terkait
                'bulan' => 'Januari',
                'pengeluaran' => 1000000.00,
                'keterangan' => 'Pembelian barang operasional',
            ],
            [
                'rekening_id' => 2,
                'bulan' => 'Februari',
                'pengeluaran' => 500000.00,
                'keterangan' => 'Pembayaran listrik',
            ],
        ];

        foreach ($pengeluarans as $pengeluaran) {
            Pengeluaran::create($pengeluaran);
        }
    }
}
