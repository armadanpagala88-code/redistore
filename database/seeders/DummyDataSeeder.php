<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AkunGame;
use Faker\Factory as Faker;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $members = [];

        // Create 10 dummy members
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'username' => $faker->unique()->userName() . rand(100, 999),
                'nama_lengkap' => $faker->name,
                'email' => $faker->unique()->safeEmail(),
                'no_telepon' => $faker->phoneNumber,
                'password' => Hash::make('password123'),
                'role' => 'Pelanggan',
                'is_aktif' => true,
                'saldo' => 0,
                'poin' => 0,
                'level' => 'Basic'
            ]);
            
            $members[] = $user;
        }

        $kategoriIds = [1, 2, 3]; // ML, FF, Genshin
        $loginVias = ['Moonton', 'Facebook', 'Google', 'VK', 'Twitter', 'Tiktok', 'Hoyoverse'];

        // Create 20 dummy accounts for sale
        for ($i = 1; $i <= 20; $i++) {
            $seller = $members[array_rand($members)];
            $kategoriId = $kategoriIds[array_rand($kategoriIds)];
            
            $judul = "Akun Sultan " . $faker->word() . " " . rand(100, 999) . " Skin";
            
            AkunGame::create([
                'user_id' => $seller->id,
                'kategori_game_id' => $kategoriId,
                'judul_akun' => $judul,
                'deskripsi_akun' => $faker->paragraph(3) . "\n\nSpesifikasi lengkap, aman 100%.",
                'harga' => rand(50, 500) * 1000, // Rp 50.000 - Rp 500.000
                'login_via' => $loginVias[array_rand($loginVias)],
                'email_akun' => "dummy_akun_{$i}@game.com",
                'password_akun' => "rahasia{$i}!",
                'catatan_akun' => "Mohon diamankan data setelah transaksi selesai.",
                'gambar_utama' => 'dummy.png',
                'status' => 'Tersedia' // Langsung disetujui untuk demo
            ]);
        }
    }
}
