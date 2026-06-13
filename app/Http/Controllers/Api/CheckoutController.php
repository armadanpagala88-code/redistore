<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\ProdukVoucher;
use App\Services\FonnteService;
use Illuminate\Support\Str;

use App\Models\DiskonVoucher;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function checkPromo(Request $request)
    {
        $request->validate([
            'kode_voucher' => 'required|string',
            'total_bayar' => 'required|numeric'
        ]);

        $voucher = DiskonVoucher::where('kode_voucher', $request->kode_voucher)->first();

        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Kode promo tidak ditemukan'], 404);
        }

        if (!$voucher->is_aktif || Carbon::parse($voucher->tgl_kadaluarsa)->isPast()) {
            return response()->json(['success' => false, 'message' => 'Kode promo sudah tidak aktif atau kadaluarsa'], 400);
        }

        if ($request->total_bayar < $voucher->minimal_beli) {
            return response()->json(['success' => false, 'message' => 'Total belanja belum memenuhi syarat minimal pembelian'], 400);
        }

        if ($voucher->kuota <= 0) {
            return response()->json(['success' => false, 'message' => 'Kuota voucher sudah habis'], 400);
        }

        $nominalDiskon = 0;
        if ($voucher->tipe === 'Persen') {
            $nominalDiskon = ($voucher->jumlah_potongan / 100) * $request->total_bayar;
        } else {
            $nominalDiskon = $voucher->jumlah_potongan;
        }

        // Jangan sampai diskon melebihi total bayar
        if ($nominalDiskon > $request->total_bayar) {
            $nominalDiskon = $request->total_bayar;
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil diterapkan',
            'data' => [
                'diskon_voucher_id' => $voucher->id,
                'kode_voucher' => $voucher->kode_voucher,
                'nominal_diskon' => $nominalDiskon,
                'total_setelah_diskon' => $request->total_bayar - $nominalDiskon
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id_game' => 'required|string',
            'produk_voucher_id' => 'required|exists:produk_vouchers,id',
            'no_whatsapp' => 'required|string',
            'zone_id' => 'nullable|string',
            'kode_voucher' => 'nullable|string'
        ]);

        $produk = ProdukVoucher::with('kategori')->findOrFail($request->produk_voucher_id);
        
        $trxId = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        
        $totalBayar = $produk->harga_jual;
        $nominalDiskon = 0;
        $diskonVoucherId = null;

        // Cek jika menggunakan promo
        if ($request->filled('kode_voucher')) {
            $voucher = DiskonVoucher::where('kode_voucher', $request->kode_voucher)
                ->where('is_aktif', true)
                ->whereDate('tgl_kadaluarsa', '>=', Carbon::today())
                ->first();

            if ($voucher && $totalBayar >= $voucher->minimal_beli && $voucher->kuota > 0) {
                if ($voucher->tipe === 'Persen') {
                    $nominalDiskon = ($voucher->jumlah_potongan / 100) * $totalBayar;
                } else {
                    $nominalDiskon = $voucher->jumlah_potongan;
                }
                
                if ($nominalDiskon > $totalBayar) {
                    $nominalDiskon = $totalBayar;
                }

                $diskonVoucherId = $voucher->id;
                $totalBayar = $totalBayar - $nominalDiskon;

                // Kurangi kuota voucher
                $voucher->decrement('kuota');
            }
        }
        
        $transaksi = Transaksi::create([
            'id' => $trxId,
            'user_id' => auth('sanctum')->id(), // akan null jika tidak login
            'user_id_game' => $request->user_id_game,
            'zone_id' => $request->zone_id,
            'no_whatsapp' => $request->no_whatsapp,
            'total_bayar' => $totalBayar,
            'diskon_voucher_id' => $diskonVoucherId,
            'nominal_diskon' => $nominalDiskon,
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

        // Integrate Midtrans
        $midtrans = new \App\Services\MidtransService();
        $customerDetails = [
            'first_name' => 'Pelanggan',
            'phone' => $request->no_whatsapp,
        ];
        $itemDetails = [
            [
                'id' => $produk->id,
                'price' => $totalBayar,
                'quantity' => 1,
                'name' => "{$produk->nominal} {$produk->kategori->nama_game}"
            ]
        ];

        $snapToken = $midtrans->createTransaction($trxId, $totalBayar, $customerDetails, $itemDetails);
        
        if ($snapToken) {
            $transaksi->snap_token = $snapToken;
            $transaksi->save();
        }

        // Kirim Notifikasi Tagihan via WA
        $msg = "Halo!\nTerima kasih telah melakukan pemesanan di *Redistore*.\n\nDetail Pesanan:\n- ID Transaksi: *$trxId*\n- Item: *{$produk->nominal} {$produk->kategori->nama_game}*\n- User ID: *{$request->user_id_game}* " . ($request->zone_id ? "({$request->zone_id})" : "") . "\n- Total Tagihan: *Rp " . number_format($totalBayar, 0, ',', '.') . "*\n\nSilakan selesaikan pembayaran Anda melalui link berikut:\n" . url("/invoice/{$trxId}");
        
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
