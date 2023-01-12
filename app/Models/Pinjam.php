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
        'es1',
        'es2',
        'es3',
        'es4',
        'nippenyetuju',
        'nippenanggungjawab',
        'nippemakai',
        'tglpengembalian',
        'jenispinjam',
    ];

    public function detailPinjaman () {
        return $this->hasMany('App\Models\DetailPinjam', 'idpinjam', 'id');
    }

    public function detailPengembalian () {
        return $this->hasMany('App\Models\DetailPengembalian', 'idpinjam', 'id');
    }

    public function detailPegawai () {
        return $this->hasOne('App\Models\Pegawai', 'nip', 'nip')->with('unitKerja')->with('jabatan')->with('userPegawai');
    }
    
    public function eselon1 () {
        return $this->hasOne('App\Models\Eselon', 'id', 'es1')->select('id', 'nama');
    }

    public function eselon2 () {
        return $this->hasOne('App\Models\Eselon', 'id', 'es2')->select('id', 'nama');
    }

    public function eselon3 () {
        return $this->hasOne('App\Models\Eselon', 'id', 'es3')->select('id', 'nama');
    }

    public function eselon4 () {
        return $this->hasOne('App\Models\Eselon', 'id', 'es4')->select('id', 'nama');
    }

    public function penanggungJawab () {
        return $this->hasOne('App\Models\pegawai', 'nip', 'nippenanggungjawab')->select('nip', 'nama');
    }

    public function pemakai () {
        return $this->hasOne('App\Models\pegawai', 'nip', 'nippemakai')->select('nip', 'nama');
    }

    public function penyetuju () {
        return $this->hasOne('App\Models\pegawai', 'nip', 'nippenyetuju')->select('nip', 'nama');
    }

    
}