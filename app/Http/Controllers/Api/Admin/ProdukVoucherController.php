<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProdukVoucher;

class ProdukVoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = ProdukVoucher::with('kategori')->orderBy('created_at', 'desc');
        if ($request->kategori_game_id) {
            $query->where('kategori_game_id', $request->kategori_game_id);
        }
        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_game_id' => 'required|exists:kategori_games,id',
            'nominal' => 'required|string',
            'harga_beli' => 'required|numeric',
            'harga_jual' => 'required|numeric',
            'is_aktif' => 'boolean'
        ]);

        $produk = ProdukVoucher::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $produk
        ]);
    }

    public function show($id)
    {
        $produk = ProdukVoucher::findOrFail($id);
        return response()->json(['success' => true, 'data' => $produk]);
    }

    public function update(Request $request, $id)
    {
        $produk = ProdukVoucher::findOrFail($id);

        $request->validate([
            'kategori_game_id' => 'required|exists:kategori_games,id',
            'nominal' => 'required|string',
            'harga_beli' => 'required|numeric',
            'harga_jual' => 'required|numeric',
            'is_aktif' => 'boolean'
        ]);

        $produk->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'data' => $produk
        ]);
    }

    public function destroy($id)
    {
        try {
            $produk = ProdukVoucher::findOrFail($id);
            $produk->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi penjualan.'
                ], 400);
            }
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk: ' . $e->getMessage()
            ], 500);
        }
    }
}
