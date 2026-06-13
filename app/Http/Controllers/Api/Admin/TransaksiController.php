<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Services\FonnteService;

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

        $transaksi = Transaksi::with('details')->findOrFail($id);
        $transaksi->status_transaksi = $request->status;
        $transaksi->save();

        // Kirim Notifikasi jika Success atau Failed
        if ($request->status === 'Success') {
            $msg = "Horee! Pembayaran pesanan *$id* telah *BERHASIL* diverifikasi.\n\nPesanan top up game Anda telah kami kirim. Terima kasih telah berbelanja di Redistore!";
            FonnteService::sendMessage($transaksi->no_whatsapp, $msg);
        } else if ($request->status === 'Failed') {
            $msg = "Mohon maaf, pembayaran untuk pesanan *$id* *GAGAL* diverifikasi.\n\nSilakan periksa kembali bukti pembayaran Anda atau hubungi Admin kami.";
            FonnteService::sendMessage($transaksi->no_whatsapp, $msg);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status transaksi berhasil diperbarui',
            'data' => $transaksi
        ]);
    }
}
