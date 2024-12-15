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
            $table->unsignedBigInteger('rekening_id');
            $table->string('bulan');
            $table->decimal('jumlah_pengeluaran', 15, 2);
            $table->string('keterangan')->nullable();
            $table->string('status')->default('Belum Disahkan');
            $table->timestamps();
        
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

