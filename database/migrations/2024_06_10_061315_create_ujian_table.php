<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ujian', function (Blueprint $table) {
            $table->bigIncrements('id_ujian');
            $table->integer('kelompok_belajar_id');
            $table->integer('paket_soal_id');
            $table->string('nama_ujian');
            $table->string('ketentuan_ujian')->nullable();
            $table->dateTime('tanggal_ujian');
            $table->integer('pembuat_ujian_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian');
    }
};
