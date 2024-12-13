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
        $table->string('bulan')->nullable()->after('rekening_id'); // Tambahkan nullable untuk kolom bulan
    });
}

public function down()
{
    Schema::table('penerimaans', function (Blueprint $table) {
        $table->dropColumn('bulan');
    });
}



};
