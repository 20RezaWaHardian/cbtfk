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
        Schema::create('pilgan_jawab', function (Blueprint $table) {
            $table->bigIncrements('id_pilgan_jawab');
            $table->integer('pilgan_id');
            $table->integer('peserta_ujian_id');
            $table->text('jawab')->nullable();
            $table->text('score')->nullable();
            $table->text('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilgan_jawab');
    }
};
