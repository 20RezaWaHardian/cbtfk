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
        Schema::create('pilgan', function (Blueprint $table) {
            $table->bigIncrements('id_pilgan');
            $table->integer('soal_id');
            $table->text('pertanyaan');
            $table->text('pil_a')->nullable();
            $table->text('pil_b')->nullable();
            $table->text('pil_c')->nullable();
            $table->text('pil_d')->nullable();
            $table->text('pil_e')->nullable();
            $table->string('kunci')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilgan');
    }
};
