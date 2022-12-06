<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    protected $table = 'kendaraan';

    protected $fillable = [
        'id',
        'idType',
        'idjeniskdrn',
        'nobpkb',
        'nomesin',
        'norangka',
        'nopolisi',
        'thnkdrn',
        'tglpajak',
        'tglmatipajak',
        'jaraktempuh',
        'idmerk',
        'warna',
        'kondisi',
    ];
}