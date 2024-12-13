<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    use HasFactory;

    protected $fillable = ['rekening', 'bank', 'saldo_saat_ini'];

    public function penerimaan()
    {
        return $this->hasMany(Penerimaan::class);
    }
}
