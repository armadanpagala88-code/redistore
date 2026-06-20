<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiskonVoucher extends Model
{
    protected $fillable = [
        'kode_voucher',
        'tipe',
        'jumlah_potongan',
        'minimal_beli',
        'kuota',
        'tgl_kadaluarsa',
        'is_aktif'
    ];
}
