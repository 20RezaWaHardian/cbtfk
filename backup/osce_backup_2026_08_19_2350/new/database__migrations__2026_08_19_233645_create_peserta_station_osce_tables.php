<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peserta_station_osce', function (Blueprint $table) {
            $table->id('id_peserta_station_osce');
            $table->unsignedBigInteger('id_jadwal_osce');
            $table->unsignedBigInteger('id_jenis_osce');
            $table->unsignedBigInteger('id_mhs_pt');
            $table->unsignedBigInteger('id_pegawai_penilai')->nullable();
            $table->unsignedBigInteger('id_station_berikutnya')->nullable();
            $table->integer('urutan_antrian')->default(1);
            $table->enum('status', ['menunggu', 'sedang_dinilai', 'selesai_dinilai', 'pindah_station', 'selesai_ujian'])->default('menunggu');
            $table->timestamp('waktu_masuk')->nullable();
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->text('catatan_perpindahan')->nullable();
            $table->timestamps();
            $table->index(['id_jadwal_osce', 'id_jenis_osce', 'status'], 'idx_peserta_station_status');
            $table->index(['id_jadwal_osce', 'id_mhs_pt'], 'idx_peserta_station_mhs');
        });

        Schema::create('nilai_peserta_station_osce', function (Blueprint $table) {
            $table->id('id_nilai_peserta_station_osce');
            $table->unsignedBigInteger('id_peserta_station_osce');
            $table->unsignedBigInteger('id_komponen_nilai_osce');
            $table->unsignedBigInteger('id_pegawai')->nullable();
            $table->integer('nilai')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['id_peserta_station_osce', 'id_komponen_nilai_osce'], 'uniq_nilai_peserta_station');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_peserta_station_osce');
        Schema::dropIfExists('peserta_station_osce');
    }
};
