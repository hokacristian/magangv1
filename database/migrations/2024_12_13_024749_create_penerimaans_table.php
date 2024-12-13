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
    Schema::create('penerimaans', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('rekening_id'); // Relasi ke tabel rekening
        $table->decimal('saldo_awal', 15, 2)->default(0); // Saldo awal
        $table->decimal('penerimaan', 15, 2)->default(0); // Penerimaan
        $table->decimal('saldo_akhir', 15, 2)->default(0); // Saldo akhir
        $table->string('keterangan')->nullable(); // Keterangan penerimaan
        $table->string('status')->default('Belum Disahkan'); // Status penerimaan
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
        Schema::dropIfExists('penerimaans');
    }
};
