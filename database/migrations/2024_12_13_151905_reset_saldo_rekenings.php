<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('rekenings', function (Blueprint $table) {
            DB::table('rekenings')->update(['saldo_saat_ini' => 0]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekenings', function (Blueprint $table) {
            //
        });
    }
};
