<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPengembalian extends Model
{
    protected $table = 'detail_pengembalian';

    protected $fillable = [
        'id',
        'idpinjam',
        'idkdrn',
        'tglkembali',
        'kmsesudah',
        'remark',
    ];

    public $timestamps = false;

    public function detailKendaraan () {
        return $this->hasOne('App\Models\Kendaraan', 'id', 'idkdrn')->with('merk')->with('type')->with('jenis')->with('foto');
    }

    public function kendaraan () {
        return $this->hasOne('App\Models\Kendaraan', 'id', 'idkdrn')->select('id', 'idtypekdrn', 'idjeniskdrn', 'idmerkkdrn', 'nopolisi', 'warna')->with('merk')->with('type')->with('jenis')->with('foto');
    }

    public function fotoPinjam () {
        return $this->hasMany('App\Models\FotoPinjam', 'reference_id', 'id')->where('type', 'Pengembalian');
    }
}