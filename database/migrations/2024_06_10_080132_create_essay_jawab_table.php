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
        Schema::create('essay_jawab', function (Blueprint $table) {
            $table->bigIncrements('id_essay_jawab');
            $table->integer('essay_id');
            $table->integer('peserta_ujian_id');
            $table->text('jawab')->nullable();
            $table->text('score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('essay_jawab');
    }
};
