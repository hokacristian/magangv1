<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $fillable = ['rekening_id', 'bulan', 'saldo_awal', 'jumlah_pengeluaran', 'keterangan', 'status', 'saldo_akhir'];

    public function rekening()
    {
        return $this->belongsTo(Rekening::class);
    }
}

