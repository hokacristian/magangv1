<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $fillable = ['rekening_id', 'bulan', 'saldo_awal', 'jumlah_pengeluaran', 'saldo_akhir', 'keterangan', 'status'];

    public function rekening()
    {
        return $this->belongsTo(Rekening::class);
    }
}

