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
            $table->string('bulan'); // Bulan pengeluaran
            $table->decimal('saldo_awal', 15, 2)->default(0); // Saldo awal rekening
            $table->decimal('jumlah_pengeluaran', 15, 2)->default(0); // Jumlah pengeluaran
            $table->string('keterangan')->nullable(); // Keterangan pengeluaran
            $table->string('status')->default('Belum Disahkan'); // Status pengeluaran
            $table->decimal('saldo_akhir', 15, 2)->nullable(); // Saldo akhir setelah pengeluaran
            $table->timestamps();

            // Foreign key ke tabel rekening
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

