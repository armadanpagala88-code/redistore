<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;

class TransaksiController extends Controller
{
    public function index()
    {
        // Mengambil semua transaksi urut terbaru
        $transaksis = Transaksi::with('details')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $transaksis
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Unpaid,Pending,Success,Failed,Cancel,Refund'
        ]);

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->status_transaksi = $request->status;
        $transaksi->save();

        return response()->json([
            'success' => true,
            'message' => 'Status transaksi berhasil diperbarui',
            'data' => $transaksi
        ]);
    }
}
