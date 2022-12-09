<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    protected $table = 'kendaraan';

    protected $fillable = [
        'id',
        'idtypekdrn',
        'idjeniskdrn',
        'nobpkb',
        'nomesin',
        'norangka',
        'nopolisi',
        'thnkdrn',
        'tglpajak',
        'tglmatipajak',
        'jaraktempuh',
        'idmerkkdrn',
        'warna',
        'kondisi',
    ];

    public function merk() {
        return $this->hasOne('App\Models\MerkKendaraan', 'id', 'idmerkkdrn');
    }

    public function jenis() {
        return $this->hasOne('App\Models\JenisKendaraan', 'id', 'idjeniskdrn');
    }

    public function type() {
        return $this->hasOne('App\Models\TypeKendaraan', 'id', 'idtypekdrn');
    }

    public function foto() {
        return $this->hasMany('App\Models\Foto', 'idkdrn', 'id')->select('id', 'idkdrn', 'urlfoto', 'tglupload', 'caption');
    }
}