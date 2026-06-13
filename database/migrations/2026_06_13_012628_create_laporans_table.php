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
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 200);
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->integer('total_transaksi')->default(0);
            $table->decimal('total_pendapatan', 15, 2)->default(0);
            $table->integer('total_voucher_terjual')->default(0);
            $table->string('produk_terlaris', 200)->nullable();
            $table->string('game_terlaris', 150)->nullable();
            $table->foreignId('digenerate_oleh')->constrained('users')->onDelete('restrict');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
