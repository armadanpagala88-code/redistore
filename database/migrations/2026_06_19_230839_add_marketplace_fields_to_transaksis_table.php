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
        Schema::table('transaksis', function (Blueprint $table) {
            $table->enum('tipe_transaksi', ['TopUp', 'BeliAkun'])->default('TopUp')->after('id');
            $table->foreignId('akun_game_id')->nullable()->after('user_id_game')->constrained('akun_games')->onDelete('set null');
            
            // Allow user_id_game to be nullable because buying accounts doesn't need it
            $table->string('user_id_game', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropForeign(['akun_game_id']);
            $table->dropColumn('akun_game_id');
            $table->dropColumn('tipe_transaksi');
            
            $table->string('user_id_game', 50)->nullable(false)->change();
        });
    }
};
