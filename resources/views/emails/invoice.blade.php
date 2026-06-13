<!DOCTYPE html>
<html>
<head>
    <title>Struk Pembelian Berhasil</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #4CAF50; margin: 0; }
        .details { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details th, .details td { text-align: left; padding: 10px; border-bottom: 1px solid #f0f0f0; }
        .details th { width: 40%; color: #555; }
        .footer { text-align: center; font-size: 12px; color: #888; margin-top: 30px; border-top: 1px solid #f0f0f0; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pembayaran Berhasil!</h1>
            <p>Terima kasih telah berbelanja di Redistore.</p>
        </div>
        
        <table class="details">
            <tr>
                <th>No. Invoice / Transaksi</th>
                <td><strong>{{ $transaksi->id }}</strong></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><span style="color: #4CAF50; font-weight: bold;">{{ $transaksi->status_transaksi }}</span></td>
            </tr>
            <tr>
                <th>Waktu Transaksi</th>
                <td>{{ $transaksi->created_at->format('d M Y, H:i') }} WIB</td>
            </tr>
            <tr>
                <th>User ID Game</th>
                <td>{{ $transaksi->user_id_game }} {{ $transaksi->zone_id ? '(' . $transaksi->zone_id . ')' : '' }}</td>
            </tr>
        </table>

        <h3>Detail Pembelian</h3>
        <table class="details">
            @foreach($transaksi->details as $detail)
            <tr>
                <th>{{ $detail->nama_game }} - {{ $detail->nama_produk }}</th>
                <td style="text-align: right;">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            
            @if($transaksi->nominal_diskon > 0)
            <tr>
                <th>Diskon Voucher</th>
                <td style="text-align: right; color: #e53935;">- Rp {{ number_format($transaksi->nominal_diskon, 0, ',', '.') }}</td>
            </tr>
            @endif

            <tr>
                <th><strong style="font-size: 16px;">Total Bayar</strong></th>
                <td style="text-align: right;"><strong style="font-size: 16px; color: #1976D2;">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</strong></td>
            </tr>
        </table>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem kami. Harap tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Redistore. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
