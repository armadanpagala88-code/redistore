<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'flash_sale_id',
        'produk_voucher_id',
        'harga_flash_sale',
        'stok_promo',
    ];

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function produk()
    {
        return $this->belongsTo(ProdukVoucher::class, 'produk_voucher_id');
    }
}
