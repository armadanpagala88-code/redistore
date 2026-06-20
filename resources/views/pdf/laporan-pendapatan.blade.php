<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendapatan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h2 {
            margin: 0;
            padding: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #666;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .summary th, .summary td {
            text-align: left;
            padding: 5px;
        }
        .summary th {
            width: 150px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
        }
        table.data th, table.data td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        table.data th {
            background-color: #f4f4f4;
            font-weight: bold;
            text-align: left;
        }
        table.data tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 11px;
            color: #777;
        }
        .total-row {
            font-weight: bold;
            background-color: #eee !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PENDAPATAN TRANSAKSI</h2>
        <p>Aplikasi Redistore</p>
        <p>
            Periode: 
            {{ $start_date ? \Carbon\Carbon::parse($start_date)->format('d M Y') : 'Awal' }} 
            s/d 
            {{ $end_date ? \Carbon\Carbon::parse($end_date)->format('d M Y') : 'Akhir' }}
        </p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <th>Total Transaksi Sukses</th>
                <td>: {{ $total_transaksi }} Transaksi</td>
            </tr>
            <tr>
                <th>Total Pendapatan</th>
                <td>: Rp {{ number_format($total_pendapatan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Tanggal Dicetak</th>
                <td>: {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="15%">ID Transaksi</th>
                <th width="15%">Pelanggan</th>
                <th width="15%">Tanggal</th>
                <th width="15%">Tipe</th>
                <th width="20%">No Whatsapp / Kontak</th>
                <th class="text-right" width="15%">Total Bayar (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $index => $trx)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $trx->id }}</td>
                <td>
                    <b>{{ $trx->user ? $trx->user->nama_lengkap : 'Tamu' }}</b><br>
                    <small style="color: #666;">{{ $trx->user ? '@'.$trx->user->username : '-' }}</small>
                </td>
                <td>{{ \Carbon\Carbon::parse($trx->tgl_transaksi)->format('d/m/Y H:i') }}</td>
                <td>{{ $trx->tipe_transaksi ?? 'Lainnya' }}</td>
                <td>{{ $trx->no_whatsapp }}</td>
                <td class="text-right">{{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data transaksi sukses pada periode ini.</td>
            </tr>
            @endforelse
            @if(count($laporan) > 0)
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL PENDAPATAN</td>
                <td class="text-right">{{ number_format($total_pendapatan, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh Sistem - Redistore
    </div>
</body>
</html>
