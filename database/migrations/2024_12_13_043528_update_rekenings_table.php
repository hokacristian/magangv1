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
        $table->dropColumn(['saldo_awal', 'penerimaan', 'pengeluaran', 'saldo_akhir']);
        $table->decimal('saldo_saat_ini', 15, 2)->default(0)->after('bank');
    });
}

public function down()
{
    Schema::table('rekenings', function (Blueprint $table) {
        $table->decimal('saldo_awal', 15, 2)->default(0);
        $table->decimal('penerimaan', 15, 2)->default(0);
        $table->decimal('pengeluaran', 15, 2)->default(0);
        $table->decimal('saldo_akhir', 15, 2)->default(0);
        $table->dropColumn('saldo_saat_ini');
    });
}

};
