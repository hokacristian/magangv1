<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('rekenings', function (Blueprint $table) {
            $table->decimal('saldo_awal', 15, 2)->default(0)->after('saldo_saat_ini'); // Saldo awal
            $table->decimal('penerimaan', 15, 2)->default(0)->after('saldo_awal'); // Jumlah penerimaan
            $table->decimal('pengeluaran', 15, 2)->default(0)->after('penerimaan'); // Jumlah pengeluaran
            $table->decimal('saldo_akhir', 15, 2)->default(0)->after('pengeluaran'); // Saldo akhir
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('rekenings', function (Blueprint $table) {
            $table->dropColumn(['saldo_awal', 'penerimaan', 'pengeluaran', 'saldo_akhir']);
        });
    }
};
