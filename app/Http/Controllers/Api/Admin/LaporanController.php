<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function exportPdf(Request $request)
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
        
        $total_transaksi = $laporan->count();
        $total_pendapatan = $laporan->sum('total_bayar');

        $data = [
            'laporan' => $laporan,
            'total_transaksi' => $total_transaksi,
            'total_pendapatan' => $total_pendapatan,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ];

        $pdf = Pdf::loadView('pdf.laporan-pendapatan', $data);
        
        return $pdf->download('laporan-pendapatan-'.date('YmdHis').'.pdf');
    }
}
