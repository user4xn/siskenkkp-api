<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eselon extends Model
{
    protected $table = 'eselon';

    protected $fillable = [
        'id',
        'nip',
        'tipe',
        'nama',
    ];
}