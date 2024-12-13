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
    Schema::create('pengeluarans', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('rekening_id'); // Relasi ke tabel rekening
        $table->string('bulan'); // Januari-Desember
        $table->decimal('pengeluaran', 15, 2); // Jumlah pengeluaran
        $table->string('keterangan'); // Keterangan pengeluaran
        $table->timestamps();

        // Foreign key untuk relasi dengan tabel rekening
        $table->foreign('rekening_id')->references('id')->on('rekenings')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluarans');
    }
};
