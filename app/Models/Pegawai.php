<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $fillable = [
        'nip',
        'nama',
        'jk',
        'alamat',
        'idbiro',
        'idjabatan',
    ];

    public function unitKerja() {
        return $this->hasOne('App\Models\UnitKerja', 'id', 'idbiro');
    }

    public function jabatan() {
        return $this->hasOne('App\Models\Jabatan', 'id', 'idjabatan');
    }

    public function userPegawai() {
        return $this->hasOne('App\Models\UserPegawai', 'nip', 'nip');
    }
}