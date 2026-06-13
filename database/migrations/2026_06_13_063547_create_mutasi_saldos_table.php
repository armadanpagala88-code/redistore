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
        Schema::create('mutasi_saldos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('jenis', ['Masuk', 'Keluar']);
            $table->decimal('nominal', 15, 2);
            $table->decimal('saldo_akhir', 15, 2);
            $table->string('keterangan')->nullable();
            $table->string('referensi_id')->nullable(); // ID Transaksi atau ID Topup
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_saldos');
    }
};
