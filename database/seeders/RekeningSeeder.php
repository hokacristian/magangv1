<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rekening;

class RekeningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rekenings = [
            [
                'rekening' => '8003380047',
                'bank' => 'BNI',
                'saldo_awal' => 0,
                'penerimaan' => 0,
                'pengeluaran' => 0,
                'saldo_akhir' => 0,
            ],
            [
                'rekening' => '1500015440363',
                'bank' => 'MANDIRI',
                'saldo_awal' => 0,
                'penerimaan' => 0,
                'pengeluaran' => 0,
                'saldo_akhir' => 0,
            ],
            [
                'rekening' => '0000011-01-30-000715-3',
                'bank' => 'BTN',
                'saldo_awal' => 0,
                'penerimaan' => 0,
                'pengeluaran' => 0,
                'saldo_akhir' => 0,
            ],
            [
                'rekening' => '0000011-01-30-000714-5',
                'bank' => 'BTN',
                'saldo_awal' => 0,
                'penerimaan' => 0,
                'pengeluaran' => 0,
                'saldo_akhir' => 0,
            ],
            [
                'rekening' => '0000011-01-30-000719-5',
                'bank' => 'BTN',
                'saldo_awal' => 0,
                'penerimaan' => 0,
                'pengeluaran' => 0,
                'saldo_akhir' => 0,
            ],
            [
                'rekening' => '7189282103',
                'bank' => 'BSI',
                'saldo_awal' => 0,
                'penerimaan' => 0,
                'pengeluaran' => 0,
                'saldo_akhir' => 0,
            ],
            [
                'rekening' => '7000000167482966',
                'bank' => 'BSI',
                'saldo_awal' => 0,
                'penerimaan' => 0,
                'pengeluaran' => 0,
                'saldo_akhir' => 0,
            ],
            [
                'rekening' => '00011-01-40-002487-7',
                'bank' => 'BTN',
                'saldo_awal' => 0,
                'penerimaan' => 0,
                'pengeluaran' => 0,
                'saldo_akhir' => 0,
            ],
            [
                'rekening' => '00011-01-40-002491-6',
                'bank' => 'BTN',
                'saldo_awal' => 0,
                'penerimaan' => 0,
                'pengeluaran' => 0,
                'saldo_akhir' => 0,
            ],
            [
                'rekening' => '00011-01-40-002009-3',
                'bank' => 'BTN',
                'saldo_awal' => 0,
                'penerimaan' => 0,
                'pengeluaran' => 0,
                'saldo_akhir' => 0,
            ],
        ];

        foreach ($rekenings as $rekening) {
            Rekening::create($rekening);
        }
    }
}
