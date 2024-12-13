<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Penerimaan;

class PenerimaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $penerimaans = [
            [
                'rekening_id' => 1, // ID rekening terkait
                'saldo_awal' => 5000000.00,
                'penerimaan' => 2000000.00,
                'saldo_akhir' => 7000000.00,
                'keterangan' => 'Setoran bulanan',
                'status' => 'Sudah Disahkan',
            ],
            [
                'rekening_id' => 2,
                'saldo_awal' => 3000000.00,
                'penerimaan' => 1000000.00,
                'saldo_akhir' => 4000000.00,
                'keterangan' => 'Dana donasi',
                'status' => 'Belum Disahkan',
            ],
        ];

        foreach ($penerimaans as $penerimaan) {
            Penerimaan::create($penerimaan);
        }
    }
}
