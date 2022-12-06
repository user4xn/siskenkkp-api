<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPegawai extends Model
{
    protected $table = 'user_pegawai';

    protected $fillable = [
        'id',
        'nip',
        'createddate',
    ];
}