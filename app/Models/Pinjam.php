<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pinjam extends Model
{
    protected $table = 'pinjam';

    protected $fillable = [
        'id',
        'nip',
        'tglpinjam',
    ];

    public function detailPinjaman () {
        return $this->hasMany('App\Models\DetailPinjam', 'idpinjam', 'id');
    }

    public function detailPengembalian () {
        return $this->hasMany('App\Models\DetailPengembalian', 'idpinjam', 'id');
    }
}