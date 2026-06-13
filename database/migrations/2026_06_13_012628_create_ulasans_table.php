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
        Schema::create('ulasans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('produk_voucher_id')->constrained('produk_vouchers')->onDelete('cascade');
            $table->string('transaksi_id', 20);
            $table->foreign('transaksi_id')->references('id')->on('transaksis')->onDelete('cascade');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('komentar')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'produk_voucher_id', 'transaksi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ulasans');
    }
};
