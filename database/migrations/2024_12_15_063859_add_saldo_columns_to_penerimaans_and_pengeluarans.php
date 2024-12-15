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
    Schema::table('penerimaans', function (Blueprint $table) {
        $table->decimal('saldo_awal', 15, 2)->default(0);
        $table->decimal('saldo_akhir', 15, 2)->default(0);
    });

    Schema::table('pengeluarans', function (Blueprint $table) {
        $table->decimal('saldo_awal', 15, 2)->default(0);
        $table->decimal('saldo_akhir', 15, 2)->default(0);
    });
}

public function down()
{
    Schema::table('penerimaans', function (Blueprint $table) {
        $table->dropColumn(['saldo_awal', 'saldo_akhir']);
    });

    Schema::table('pengeluarans', function (Blueprint $table) {
        $table->dropColumn(['saldo_awal', 'saldo_akhir']);
    });
}

};
