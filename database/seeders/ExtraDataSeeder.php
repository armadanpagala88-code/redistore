<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\ProdukVoucher;
use App\Models\Transaksi;

class ExtraDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        // Check if we have users and products
        $admin = User::where('role', 'Admin')->first();
        $member = User::where('role', 'Pelanggan')->first();
        
        $produk = ProdukVoucher::first();
        $transaksi = DB::table('transaksis')->first();

        // 1. Seed Laporan (Does not depend on product or transaction)
        if ($admin) {
            DB::table('laporans')->insert([
                [
                    'judul' => 'Laporan Penjualan Bulan Juni 2026',
                    'periode_mulai' => '2026-06-01',
                    'periode_selesai' => '2026-06-30',
                    'total_transaksi' => 125,
                    'total_pendapatan' => 15000000.00,
                    'total_voucher_terjual' => 200,
                    'produk_terlaris' => 'Mobile Legends 296 Diamonds',
                    'game_terlaris' => 'Mobile Legends',
                    'digenerate_oleh' => $admin->id,
                    'catatan' => 'Penjualan stabil menjelang musim liburan.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'judul' => 'Laporan Penjualan Bulan Juli 2026',
                    'periode_mulai' => '2026-07-01',
                    'periode_selesai' => '2026-07-31',
                    'total_transaksi' => 45,
                    'total_pendapatan' => 4500000.00,
                    'total_voucher_terjual' => 80,
                    'produk_terlaris' => 'Free Fire 140 Diamonds',
                    'game_terlaris' => 'Free Fire',
                    'digenerate_oleh' => $admin->id,
                    'catatan' => 'Laporan sementara untuk bulan berjalan.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            ]);
            $this->command->info('Seed Laporan berhasil.');
        }

        if ($member && $produk) {
            // 2. Seed Rekomendasi
            DB::table('rekomendasis')->insert([
                [
                    'user_id' => $member->id,
                    'produk_voucher_id' => $produk->id,
                    'skor' => 0.95,
                    'alasan' => 'produk_populer',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            ]);
            $this->command->info('Seed Rekomendasi berhasil.');

            // 3. Seed Ulasan
            if ($transaksi) {
                // Remove existing if any to avoid unique constraint violation
                DB::table('ulasans')->where([
                    'user_id' => $member->id,
                    'produk_voucher_id' => $produk->id,
                    'transaksi_id' => $transaksi->id
                ])->delete();

                DB::table('ulasans')->insert([
                    [
                        'user_id' => $member->id,
                        'produk_voucher_id' => $produk->id,
                        'transaksi_id' => $transaksi->id,
                        'rating' => 5,
                        'komentar' => 'Proses pengiriman sangat cepat dan aman, terima kasih!',
                        'is_aktif' => true,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]
                ]);
                $this->command->info('Seed Ulasan berhasil.');
            } else {
                $this->command->warn('Tidak ada transaksi, Ulasan tidak dibuat.');
            }
        } else {
            $this->command->warn('Member atau Produk tidak ditemukan. Rekomendasi & Ulasan tidak dibuat.');
        }
    }
}
