<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    use HasFactory;

    protected $fillable = ['rekening', 'bank', 'saldo_saat_ini', 'saldo_awal', 'penerimaan', 'pengeluaran', 'saldo_akhir'];

    // Relasi dengan model Pengeluaran
    public function pengeluarans()
    {
        return $this->hasMany(Pengeluaran::class);
    }

    // Relasi dengan model Penerimaan
    public function penerimaans()
    {
        return $this->hasMany(Penerimaan::class);
    }

    // Tambah saldo dan update semua kolom terkait
    public function tambahSaldo($jumlah)
    {
        $this->saldo_awal = $this->saldo_awal ?? $this->saldo_saat_ini;
        $this->penerimaan += $jumlah;
        $this->saldo_saat_ini += $jumlah;
        $this->saldo_akhir = $this->saldo_saat_ini;
        $this->save();
    }

    // Kurangi saldo dan update semua kolom terkait
    public function kurangiSaldo($jumlah)
    {
        $this->saldo_awal = $this->saldo_awal ?? $this->saldo_saat_ini;
        $this->pengeluaran += $jumlah;
        $this->saldo_saat_ini -= $jumlah;
        $this->saldo_akhir = $this->saldo_saat_ini;
        $this->save();
    }

    // Reset saldo untuk testing/debugging
    public function resetSaldo()
    {
        $this->update([
            'saldo_awal' => 0,
            'penerimaan' => 0,
            'pengeluaran' => 0,
            'saldo_saat_ini' => 0,
            'saldo_akhir' => 0,
        ]);
    }
}
