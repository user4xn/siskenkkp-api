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

    public $timestamps = false;

    public function detailKendaraan () {
        return $this->hasOne('App\Models\Kendaraan', 'id', 'idkdrn')->with('merk')->with('type')->with('jenis')->with('foto');
    }
}