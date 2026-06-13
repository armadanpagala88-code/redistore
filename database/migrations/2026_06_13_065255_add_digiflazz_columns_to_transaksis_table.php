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
            $table->string('sn')->nullable()->after('status_transaksi');
            $table->string('pesan_provider')->nullable()->after('sn');
            $table->string('status_provider')->nullable()->after('pesan_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['sn', 'pesan_provider', 'status_provider']);
        });
    }
};
