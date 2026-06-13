<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DigiflazzService;
use App\Models\KategoriGame;
use App\Models\ProdukVoucher;
use Illuminate\Support\Str;

class DigiflazzSyncController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'margin_tipe' => 'required|in:flat,persen',
            'margin_nilai' => 'required|numeric'
        ]);

        $marginTipe = $request->margin_tipe;
        $marginNilai = $request->margin_nilai;

        $digiflazz = new DigiflazzService();
        $products = $digiflazz->getPriceList();

        if (empty($products)) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data dari Digiflazz. Periksa koneksi atau kredensial API.'], 500);
        }

        $countNewKategori = 0;
        $countNewProduk = 0;
        $countUpdateProduk = 0;

        foreach ($products as $item) {
            // Hanya ambil produk yang bertipe Games atau sesuai kebutuhan
            if ($item['category'] !== 'Games') continue;
            if (!$item['buyer_product_status']) continue; // Skip jika gangguan/kosong

            // 1. Cek atau Buat Kategori Game (berdasarkan brand Digiflazz)
            $brand = $item['brand'];
            $slug = Str::slug($brand);

            $kategori = KategoriGame::firstOrCreate(
                ['slug' => $slug],
                [
                    'nama_game' => $brand,
                    'deskripsi' => 'Top up ' . $brand . ' instan.',
                    'is_aktif' => true
                ]
            );

            if ($kategori->wasRecentlyCreated) {
                $countNewKategori++;
            }

            // 2. Hitung harga jual
            $hargaBeli = $item['price'];
            $hargaJual = $hargaBeli;

            if ($marginTipe === 'flat') {
                $hargaJual = $hargaBeli + $marginNilai;
            } else {
                // Persen
                $hargaJual = $hargaBeli + ($hargaBeli * ($marginNilai / 100));
            }

            // 3. Cek atau Buat/Update Produk Voucher (Gunakan sku_code sebagai acuan unik yang ditaruh di nominal)
            $produk = ProdukVoucher::where('kategori_game_id', $kategori->id)
                                   ->where('nominal', $item['buyer_sku_code'])
                                   ->first();

            if ($produk) {
                // Update harga
                $produk->harga_beli = $hargaBeli;
                $produk->harga_jual = $hargaJual;
                $produk->deskripsi = $item['product_name'];
                $produk->is_aktif = true;
                $produk->save();
                $countUpdateProduk++;
            } else {
                // Buat baru
                ProdukVoucher::create([
                    'kategori_game_id' => $kategori->id,
                    'nominal' => $item['buyer_sku_code'],
                    'harga_beli' => $hargaBeli,
                    'harga_jual' => $hargaJual,
                    'deskripsi' => $item['product_name'], // Tampilkan nama asli dr digiflazz sebagai deskripsi produk
                    'is_aktif' => true
                ]);
                $countNewProduk++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi berhasil',
            'data' => [
                'kategori_baru' => $countNewKategori,
                'produk_baru' => $countNewProduk,
                'produk_diupdate' => $countUpdateProduk
            ]
        ]);
    }
}
