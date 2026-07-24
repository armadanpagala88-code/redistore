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
        if (!Schema::hasColumn('akun_games', 'alasan_ditolak')) {
            Schema::table('akun_games', function (Blueprint $table) {
                $table->text('alasan_ditolak')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akun_games', function (Blueprint $table) {
            $table->dropColumn('alasan_ditolak');
        });
    }
};
