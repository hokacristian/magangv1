<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pengeluarans', function (Blueprint $table) {
            if (!Schema::hasColumn('pengeluarans', 'tanggal')) {
                $table->date('tanggal')->nullable()->after('bulan');
            }
            if (!Schema::hasColumn('pengeluarans', 'tahun')) {
                $table->year('tahun')->nullable()->after('tanggal');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->dropColumn(['tanggal', 'tahun']);
        });
    }
};
