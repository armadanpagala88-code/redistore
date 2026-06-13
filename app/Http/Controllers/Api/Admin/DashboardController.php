<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        $totalPendapatan = Transaksi::where('status_transaksi', 'Success')
                            ->sum('total_bayar');

        $transaksiSukses = Transaksi::where('status_transaksi', 'Success')->count();
        $transaksiPending = Transaksi::whereIn('status_transaksi', ['Unpaid', 'Pending'])->count();

        // Data for Sales Chart (last 30 days)
        $startDate = Carbon::now()->subDays(29)->startOfDay();
        
        $salesData = Transaksi::where('status_transaksi', 'Success')
            ->where('tgl_transaksi', '>=', $startDate)
            ->select(
                DB::raw('DATE(tgl_transaksi) as date'),
                DB::raw('SUM(total_bayar) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // Format data to ensure all 30 days are present
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date' => $dateStr,
                'total' => 0
            ];
        }

        foreach ($salesData as $data) {
            foreach ($chartData as &$item) {
                if ($item['date'] == $data->date) {
                    $item['total'] = (float)$data->total;
                    break;
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_pendapatan' => $totalPendapatan,
                'transaksi_sukses' => $transaksiSukses,
                'transaksi_pending' => $transaksiPending,
                'chart_data' => $chartData
            ]
        ]);
    }
}
