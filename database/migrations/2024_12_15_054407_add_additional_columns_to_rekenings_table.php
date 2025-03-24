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
            if (!Schema::hasColumn('rekenings', 'saldo_awal')) {
                $table->decimal('saldo_awal', 15, 2)->default(0)->after('saldo_saat_ini'); // Saldo awal
            }
            if (!Schema::hasColumn('rekenings', 'penerimaan')) {
                $table->decimal('penerimaan', 15, 2)->default(0)->after('saldo_awal'); // Jumlah penerimaan
            }
            if (!Schema::hasColumn('rekenings', 'pengeluaran')) {
                $table->decimal('pengeluaran', 15, 2)->default(0)->after('penerimaan'); // Jumlah pengeluaran
            }
            if (!Schema::hasColumn('rekenings', 'saldo_akhir')) {
                $table->decimal('saldo_akhir', 15, 2)->default(0)->after('pengeluaran'); // Saldo akhir
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('rekenings', function (Blueprint $table) {
            if (Schema::hasColumn('rekenings', 'saldo_awal')) {
                $table->dropColumn('saldo_awal');
            }
            if (Schema::hasColumn('rekenings', 'penerimaan')) {
                $table->dropColumn('penerimaan');
            }
            if (Schema::hasColumn('rekenings', 'pengeluaran')) {
                $table->dropColumn('pengeluaran');
            }
            if (Schema::hasColumn('rekenings', 'saldo_akhir')) {
                $table->dropColumn('saldo_akhir');
            }
        });
    }
};
