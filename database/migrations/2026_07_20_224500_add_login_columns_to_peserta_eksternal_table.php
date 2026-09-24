<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peserta_eksternal', function (Blueprint $table) {
            if (!Schema::hasColumn('peserta_eksternal', 'username')) {
                $table->string('username')->nullable()->after('id_pmb');
            }

            if (!Schema::hasColumn('peserta_eksternal', 'password_plain')) {
                $table->string('password_plain')->nullable()->after('username');
            }
        });
    }

    public function down(): void
    {
        Schema::table('peserta_eksternal', function (Blueprint $table) {
            if (Schema::hasColumn('peserta_eksternal', 'password_plain')) {
                $table->dropColumn('password_plain');
            }

            if (Schema::hasColumn('peserta_eksternal', 'username')) {
                $table->dropColumn('username');
            }
        });
    }
};