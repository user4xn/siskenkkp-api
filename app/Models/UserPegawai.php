<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPegawai extends Model
{
    protected $table = 'user_pegawai';

    protected $fillable = [
        'id',
        'userid',
        'nip',
    ];

    public function detail() {
        return $this->hasOne('App\Models\Pegawai', 'nip', 'nip')->with('unitkerja')->with('jabatan');
    }
    
    public function user() {
        return $this->hasOne('App\Models\User', 'id', 'userid');
    }
}