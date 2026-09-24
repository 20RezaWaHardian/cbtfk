<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soal', function (Blueprint $table) {
            $table->text('komentar_validasi')->nullable();
            $table->timestamp('diperbaiki_at')->nullable();
            $table->timestamp('diajukan_ulang_at')->nullable();
            $table->string('sidik_penolakan', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('soal', function (Blueprint $table) {
            $table->dropColumn(['komentar_validasi', 'diperbaiki_at', 'diajukan_ulang_at', 'sidik_penolakan']);
        });
    }
};