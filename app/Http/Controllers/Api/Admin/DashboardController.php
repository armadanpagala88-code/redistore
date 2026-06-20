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

        // Data for Sales Chart (12 Months of Current Year)
        $salesData = Transaksi::where('status_transaksi', 'Success')
            ->whereYear('tgl_transaksi', $thisYear)
            ->select(
                DB::raw('MONTH(tgl_transaksi) as month'),
                DB::raw('SUM(total_bayar) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get();

        // Format data to ensure all 12 months are present
        $chartData = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = [
                'date' => $months[$i - 1],
                'month_num' => $i,
                'total' => 0
            ];
        }

        foreach ($salesData as $data) {
            foreach ($chartData as &$item) {
                if ($item['month_num'] == $data->month) {
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
