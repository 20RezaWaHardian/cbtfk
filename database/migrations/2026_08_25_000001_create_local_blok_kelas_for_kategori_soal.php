<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('semester')) {
            Schema::create('semester', function (Blueprint $table) {
                $table->string('id_semester', 5)->primary();
                $table->date('tgl_mulai')->nullable();
                $table->date('tgl_selesai')->nullable();
                $table->boolean('periode_aktif')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('blok')) {
            Schema::create('blok', function (Blueprint $table) {
                $table->bigIncrements('id_blok');
                $table->string('nama_blok');
                $table->year('tahun')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('kelas')) {
            Schema::create('kelas', function (Blueprint $table) {
                $table->bigIncrements('id_kelas');
                $table->unsignedBigInteger('id_blok');
                $table->string('id_semester', 5);
                $table->string('kode_kelas')->nullable();
                $table->integer('jumlah_peserta')->default(0);
                $table->unsignedBigInteger('id_kelas_parent')->nullable();
                $table->timestamps();
                $table->index(['id_blok', 'id_semester']);
            });
        }

        if (Schema::hasTable('kategori_soal') && ! Schema::hasColumn('kategori_soal', 'id_kelas')) {
            Schema::table('kategori_soal', function (Blueprint $table) {
                $table->unsignedBigInteger('id_kelas')->nullable()->after('nama_kategori');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kategori_soal') && Schema::hasColumn('kategori_soal', 'id_kelas')) {
            Schema::table('kategori_soal', function (Blueprint $table) {
                $table->dropColumn('id_kelas');
            });
        }

        Schema::dropIfExists('kelas');
        Schema::dropIfExists('blok');
        Schema::dropIfExists('semester');
    }
};