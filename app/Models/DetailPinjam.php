<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPinjam extends Model
{
    protected $table = 'detail_pinjam';

    protected $fillable = [
        'id',
        'idpinjam',
        'idkdrn',
        'tglpinjam',
        'kmsebelum',
        'remark',
    ];
}