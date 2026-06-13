<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MutasiSaldo;
use App\Models\Transaksi;

class MemberController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Get last 5 mutasi saldo
        $mutasi = MutasiSaldo::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get last 5 transaksi pembelian
        $transaksi = Transaksi::with(['details'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'mutasi_terakhir' => $mutasi,
                'transaksi_terakhir' => $transaksi
            ]
        ]);
    }
}
