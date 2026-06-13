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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->string('id', 20)->primary(); // TRX001
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('tgl_transaksi')->useCurrent();
            $table->string('user_id_game', 50);
            $table->string('zone_id', 50)->nullable();
            $table->string('no_whatsapp', 20);
            $table->decimal('total_bayar', 15, 2)->default(0);
            $table->foreignId('diskon_voucher_id')->nullable()->constrained('diskon_vouchers')->onDelete('set null');
            $table->string('snap_token', 255)->nullable();
            $table->enum('status_transaksi', ['Unpaid', 'Pending', 'Success', 'Failed', 'Cancel', 'Refund'])->default('Unpaid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
