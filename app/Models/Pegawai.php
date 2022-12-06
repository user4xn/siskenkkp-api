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
        'createddate',
        'updateddate',
        'alamat',
        'idbiro',
        'idjabatan',
    ];
}