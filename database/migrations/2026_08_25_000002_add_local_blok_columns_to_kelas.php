<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            if (! Schema::hasColumn('kelas', 'id_blok')) {
                $table->unsignedBigInteger('id_blok')->nullable()->after('id_kelas');
            }

            if (! Schema::hasColumn('kelas', 'id_semester')) {
                $table->string('id_semester', 5)->nullable()->after('id_blok');
            }

            if (! Schema::hasColumn('kelas', 'kode_kelas')) {
                $table->string('kode_kelas')->nullable()->after('id_semester');
            }

            if (! Schema::hasColumn('kelas', 'jumlah_peserta')) {
                $table->integer('jumlah_peserta')->default(0)->after('kode_kelas');
            }

            if (! Schema::hasColumn('kelas', 'id_kelas_parent')) {
                $table->unsignedBigInteger('id_kelas_parent')->nullable()->after('jumlah_peserta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            foreach (['id_kelas_parent', 'jumlah_peserta', 'kode_kelas', 'id_semester', 'id_blok'] as $column) {
                if (Schema::hasColumn('kelas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};