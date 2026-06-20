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
        Schema::create('akun_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kategori_game_id')->constrained('kategori_games')->onDelete('cascade');
            $table->string('judul_akun');
            $table->text('deskripsi_akun');
            $table->decimal('harga', 15, 2);
            $table->string('login_via'); // e.g. Moonton, Facebook, Google
            $table->string('email_akun');
            $table->string('password_akun');
            $table->text('catatan_akun')->nullable();
            $table->string('gambar_utama')->nullable();
            $table->enum('status', ['Pending', 'Tersedia', 'Terjual', 'Ditolak'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akun_games');
    }
};
