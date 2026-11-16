<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('akun_orang_tua')) {
            throw new RuntimeException('Import database/legacy/schema.sql before adding parent account login fields.');
        }

        Schema::table('akun_orang_tua', function (Blueprint $table): void {
            if (! Schema::hasColumn('akun_orang_tua', 'email')) {
                $table->string('email', 191)->nullable()->unique('uq_akun_orang_tua_email')->after('no_hp');
            }
            if (! Schema::hasColumn('akun_orang_tua', 'google_id')) {
                $table->string('google_id', 191)->nullable()->unique('uq_akun_orang_tua_google_id')->after('email');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('akun_orang_tua')) {
            return;
        }

        Schema::table('akun_orang_tua', function (Blueprint $table): void {
            if (Schema::hasColumn('akun_orang_tua', 'google_id')) {
                $table->dropUnique('uq_akun_orang_tua_google_id');
                $table->dropColumn('google_id');
            }
            if (Schema::hasColumn('akun_orang_tua', 'email')) {
                $table->dropUnique('uq_akun_orang_tua_email');
                $table->dropColumn('email');
            }
        });
    }
};
