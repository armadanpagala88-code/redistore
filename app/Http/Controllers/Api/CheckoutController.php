<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\ProdukVoucher;
use App\Services\FonnteService;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'user_id_game' => 'required|string',
            'produk_voucher_id' => 'required|exists:produk_vouchers,id',
            'no_whatsapp' => 'required|string',
            'zone_id' => 'nullable|string'
        ]);

        $produk = ProdukVoucher::with('kategori')->findOrFail($request->produk_voucher_id);
        
        $trxId = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        
        // Cek jika produk punya diskon bisa dikembangkan di sini
        $totalBayar = $produk->harga_jual;
        
        $transaksi = Transaksi::create([
            'id' => $trxId,
            'user_id' => auth('sanctum')->id(), // akan null jika tidak login
            'user_id_game' => $request->user_id_game,
            'zone_id' => $request->zone_id,
            'no_whatsapp' => $request->no_whatsapp,
            'total_bayar' => $totalBayar,
            'status_transaksi' => 'Unpaid'
        ]);

        DetailTransaksi::create([
            'transaksi_id' => $trxId,
            'produk_voucher_id' => $produk->id,
            'nama_produk' => $produk->nominal,
            'nama_game' => $produk->kategori->nama_game,
            'harga_satuan' => $produk->harga_jual,
            'jumlah' => 1,
            'subtotal' => $produk->harga_jual
        ]);

        // Kirim Notifikasi Tagihan via WA
        $msg = "Halo!\nTerima kasih telah melakukan pemesanan di *Redistore*.\n\nDetail Pesanan:\n- ID Transaksi: *$trxId*\n- Item: *{$produk->nominal} {$produk->kategori->nama_game}*\n- User ID: *{$request->user_id_game}* " . ($request->zone_id ? "({$request->zone_id})" : "") . "\n- Total Tagihan: *Rp " . number_format($totalBayar, 0, ',', '.') . "*\n\nSilakan selesaikan pembayaran dengan mentransfer ke rekening BCA *1234567890 a.n Redistore*.\nUpload bukti pembayaran Anda melalui link berikut:\n" . url("/invoice/{$trxId}");
        
        FonnteService::sendMessage($request->no_whatsapp, $msg);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat',
            'data' => $transaksi
        ]);
    }

    public function show($id)
    {
        $transaksi = Transaksi::with('details')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    public function uploadBukti(Request $request, $id)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $transaksi = Transaksi::findOrFail($id);
        
        if ($request->hasFile('bukti_pembayaran')) {
            $file = $request->file('bukti_pembayaran');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/bukti'), $filename);
            
            $transaksi->bukti_pembayaran = $filename;
            $transaksi->status_transaksi = 'Pending'; // After upload, waiting for admin approval
            $transaksi->save();

            // Kirim notifikasi WA
            $msg = "Terima kasih! Bukti pembayaran untuk pesanan *$id* telah kami terima.\n\nPesanan Anda sedang diverifikasi oleh Admin. Kami akan mengabari Anda jika pesanan telah sukses diproses.";
            FonnteService::sendMessage($transaksi->no_whatsapp, $msg);

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diunggah',
                'data' => $transaksi
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Gagal upload file'], 400);
    }
}
