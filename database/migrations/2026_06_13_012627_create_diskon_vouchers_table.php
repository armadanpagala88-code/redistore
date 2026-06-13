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
        Schema::create('diskon_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_voucher', 50)->unique();
            $table->enum('tipe', ['Persen', 'Nominal'])->default('Persen');
            $table->decimal('jumlah_potongan', 15, 2)->default(0);
            $table->decimal('minimal_beli', 15, 2)->default(0);
            $table->integer('kuota')->default(1);
            $table->date('tgl_kadaluarsa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diskon_vouchers');
    }
};
