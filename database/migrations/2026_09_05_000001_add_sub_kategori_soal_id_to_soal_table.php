<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('soal', 'sub_kategori_soal_id')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->unsignedBigInteger('sub_kategori_soal_id')->nullable()->after('kategori_soal_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('soal', 'sub_kategori_soal_id')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('sub_kategori_soal_id');
            });
        }
    }
};
