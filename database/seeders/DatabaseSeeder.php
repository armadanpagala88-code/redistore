<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        \App\Models\User::insert([
            [
                'id' => 1,
                'username' => 'admin',
                'password' => bcrypt('password'),
                'nama_lengkap' => 'Admin Utama',
                'email' => 'admin@redistore.com',
                'role' => 'Admin',
                'is_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'username' => 'owner',
                'password' => bcrypt('password'),
                'nama_lengkap' => 'Pemilik Toko',
                'email' => 'owner@redistore.com',
                'role' => 'Owner',
                'is_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        \App\Models\KategoriGame::insert([
            ['id' => 1, 'nama_game' => 'Mobile Legends', 'slug' => 'mobile-legends', 'gambar_logo' => 'ml.png', 'deskripsi' => 'Top up Diamond MLBB murah dan cepat', 'is_aktif' => true],
            ['id' => 2, 'nama_game' => 'Free Fire', 'slug' => 'free-fire', 'gambar_logo' => 'ff.png', 'deskripsi' => 'Top up Diamond FF aman', 'is_aktif' => true],
            ['id' => 3, 'nama_game' => 'Genshin Impact', 'slug' => 'genshin-impact', 'gambar_logo' => 'gi.png', 'deskripsi' => 'Genesis Crystals murah', 'is_aktif' => true],
        ]);

        \App\Models\ProdukVoucher::insert([
            ['id' => 1, 'kategori_game_id' => 1, 'nominal' => '86 Diamonds', 'harga_beli' => 15000, 'harga_jual' => 20000, 'stok' => 100, 'is_aktif' => true],
            ['id' => 2, 'kategori_game_id' => 1, 'nominal' => '172 Diamonds', 'harga_beli' => 30000, 'harga_jual' => 40000, 'stok' => 100, 'is_aktif' => true],
            ['id' => 3, 'kategori_game_id' => 2, 'nominal' => '140 Diamonds', 'harga_beli' => 16000, 'harga_jual' => 20000, 'stok' => 50, 'is_aktif' => true],
        ]);

        \App\Models\DiskonVoucher::insert([
            ['id' => 1, 'kode_voucher' => 'DISKONMARET', 'tipe' => 'Persen', 'jumlah_potongan' => 10.00, 'minimal_beli' => 50000, 'kuota' => 100, 'tgl_kadaluarsa' => '2026-12-31'],
            ['id' => 2, 'kode_voucher' => 'HEMATGAS', 'tipe' => 'Nominal', 'jumlah_potongan' => 5000.00, 'minimal_beli' => 30000, 'kuota' => 50, 'tgl_kadaluarsa' => '2026-12-31'],
        ]);

        \App\Models\Transaksi::insert([
            ['id' => 'TRX001', 'user_id' => null, 'tgl_transaksi' => '2026-04-16 14:00:00', 'user_id_game' => '12345678', 'zone_id' => '1234', 'no_whatsapp' => '62812345678', 'total_bayar' => 18000, 'status_transaksi' => 'Success'],
            ['id' => 'TRX002', 'user_id' => null, 'tgl_transaksi' => '2026-04-16 14:10:00', 'user_id_game' => '87654321', 'zone_id' => '4321', 'no_whatsapp' => '62898765432', 'total_bayar' => 35000, 'status_transaksi' => 'Unpaid'],
        ]);

        \App\Models\DetailTransaksi::insert([
            ['id' => 1, 'transaksi_id' => 'TRX001', 'produk_voucher_id' => 1, 'nama_produk' => '86 Diamonds', 'nama_game' => 'Mobile Legends', 'harga_satuan' => 18000, 'jumlah' => 1, 'subtotal' => 18000],
            ['id' => 2, 'transaksi_id' => 'TRX002', 'produk_voucher_id' => 2, 'nama_produk' => '172 Diamonds', 'nama_game' => 'Mobile Legends', 'harga_satuan' => 35000, 'jumlah' => 1, 'subtotal' => 35000],
        ]);
    }
}
