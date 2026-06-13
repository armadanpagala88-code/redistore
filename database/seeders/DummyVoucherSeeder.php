<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriGame;
use App\Models\ProdukVoucher;
use Illuminate\Support\Str;

class DummyVoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Buat Dummy Kategori
        $categories = [
            [
                'nama_game' => 'Mobile Legends',
                'deskripsi' => 'Top Up Diamond Mobile Legends Resmi',
                'gambar_logo' => 'https://placehold.co/400x400/1e3a8a/ffffff?text=MLBB',
                'is_aktif' => true,
                'slug' => 'mobile-legends',
            ],
            [
                'nama_game' => 'Free Fire',
                'deskripsi' => 'Top Up Diamond Free Fire Cepat & Murah',
                'gambar_logo' => 'https://placehold.co/400x400/ea580c/ffffff?text=FF',
                'is_aktif' => true,
                'slug' => 'free-fire',
            ],
            [
                'nama_game' => 'PUBG Mobile',
                'deskripsi' => 'Beli UC PUBG Mobile Global',
                'gambar_logo' => 'https://placehold.co/400x400/eab308/ffffff?text=PUBGM',
                'is_aktif' => true,
                'slug' => 'pubg-mobile',
            ],
            [
                'nama_game' => 'Genshin Impact',
                'deskripsi' => 'Top Up Genesis Crystal Genshin Impact',
                'gambar_logo' => 'https://placehold.co/400x400/3b82f6/ffffff?text=Genshin',
                'is_aktif' => true,
                'slug' => 'genshin-impact',
            ],
            [
                'nama_game' => 'Valorant',
                'deskripsi' => 'Top Up Valorant Points (VP) Resmi Riot',
                'gambar_logo' => 'https://placehold.co/400x400/ef4444/ffffff?text=Valorant',
                'is_aktif' => true,
                'slug' => 'valorant',
            ],
        ];

        $kategoriIds = [];

        foreach ($categories as $cat) {
            $created = KategoriGame::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
            $kategoriIds[] = $created->id;
        }

        // 2. Buat 50 Dummy Produk Voucher (10 per kategori)
        $denominations = [
            'Mobile Legends' => [5, 12, 50, 70, 140, 284, 355, 429, 716, 1446],
            'Free Fire' => [5, 12, 50, 70, 140, 355, 720, 1450, 2180, 3640],
            'PUBG Mobile' => [30, 60, 325, 660, 1800, 3850, 8100, 16200, 24300, 32400],
            'Genshin Impact' => [60, 300, 980, 1980, 3280, 6480, 'Blessing of the Welkin Moon', 'Gnostic Hymn', 'Gnostic Chorus', 'Premium Bundle'],
            'Valorant' => [125, 420, 700, 1375, 2400, 4000, 8150, 10500, 12000, 15000],
        ];

        $currencies = [
            'Mobile Legends' => 'Diamonds',
            'Free Fire' => 'Diamonds',
            'PUBG Mobile' => 'UC',
            'Genshin Impact' => 'Genesis Crystals',
            'Valorant' => 'VP',
        ];

        $basePrices = [
            'Mobile Legends' => 300, // Rp 300 per diamond est
            'Free Fire' => 150,
            'PUBG Mobile' => 200,
            'Genshin Impact' => 250,
            'Valorant' => 120,
        ];

        foreach ($categories as $index => $cat) {
            $catId = $kategoriIds[$index];
            $gameName = $cat['nama_game'];
            $currency = $currencies[$gameName];
            $basePrice = $basePrices[$gameName];

            $denoms = $denominations[$gameName];

            foreach ($denoms as $denom) {
                // Determine nominal and price
                if (is_numeric($denom)) {
                    $nominalString = $denom . ' ' . $currency;
                    $hargaBeli = $denom * $basePrice;
                } else {
                    $nominalString = $denom;
                    $hargaBeli = 70000 + rand(10000, 50000); // Random base for special items
                }

                $hargaJual = $hargaBeli + ($hargaBeli * 0.15); // 15% profit margin
                
                // Pembulatan
                $hargaBeli = round($hargaBeli, -2);
                $hargaJual = round($hargaJual, -2);

                ProdukVoucher::create([
                    'kategori_game_id' => $catId,
                    'nominal' => $nominalString,
                    'harga_beli' => $hargaBeli,
                    'harga_jual' => $hargaJual,
                    'is_aktif' => true,
                ]);
            }
        }
    }
}
