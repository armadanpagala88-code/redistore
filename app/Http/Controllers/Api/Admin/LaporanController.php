<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        $query = Transaksi::with(['details'])->where('status_transaksi', 'Success');

        if ($request->filled('start_date')) {
            $query->whereDate('tgl_transaksi', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tgl_transaksi', '<=', $request->end_date);
        }

        $laporan = $query->orderBy('tgl_transaksi', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $laporan
        ]);
    }
}
