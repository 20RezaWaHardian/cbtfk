<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'usertype')) {
                $table->string('usertype')->nullable()->after('name');
            }

            if (!Schema::hasColumn('users', 'asal_user')) {
                $table->string('asal_user')->nullable()->after('id_asal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'asal_user')) {
                $table->dropColumn('asal_user');
            }

            if (Schema::hasColumn('users', 'usertype')) {
                $table->dropColumn('usertype');
            }

            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};