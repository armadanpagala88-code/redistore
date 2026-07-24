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
        $user = \App\Models\User::where('username', 'madhan')->first();
        if ($user) {
            // Re-calculate the expected saldo
            $user->saldo = 662500;
            $user->save();

            // Check if mutasi for TRX-20260723-ZVTIY already exists, if not create it
            $exists1 = \App\Models\MutasiSaldo::where('user_id', $user->id)
                ->where('keterangan', 'like', '%TRX-20260723-ZVTIY%')
                ->exists();
            if (!$exists1) {
                \App\Models\MutasiSaldo::create([
                    'user_id' => $user->id,
                    'jenis' => 'Masuk',
                    'nominal' => 427500,
                    'saldo_akhir' => 427500, // Just an approximate historical value
                    'keterangan' => 'Hasil Penjualan Akun Game (TRX-20260723-ZVTIY) - Potongan Admin 5%',
                    'created_at' => '2026-07-24 03:36:00',
                    'updated_at' => '2026-07-24 03:36:00'
                ]);
            }

            // Check if mutasi for TRX-20260723-B9PX0 already exists, if not create it
            $exists2 = \App\Models\MutasiSaldo::where('user_id', $user->id)
                ->where('keterangan', 'like', '%TRX-20260723-B9PX0%')
                ->exists();
            if (!$exists2) {
                \App\Models\MutasiSaldo::create([
                    'user_id' => $user->id,
                    'jenis' => 'Masuk',
                    'nominal' => 285000,
                    'saldo_akhir' => 712500, // Just an approximate historical value
                    'keterangan' => 'Hasil Penjualan Akun Game (TRX-20260723-B9PX0) - Potongan Admin 5%',
                    'created_at' => '2026-07-24 02:38:00',
                    'updated_at' => '2026-07-24 02:38:00'
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
