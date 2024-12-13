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
        Schema::create('rekenings', function (Blueprint $table) {
            $table->id();
            $table->string('rekening')->unique();
            $table->string('bank');
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('penerimaan', 15, 2)->default(0);
            $table->decimal('pengeluaran', 15, 2)->default(0);
            $table->decimal('saldo_akhir', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekenings');
    }
};
