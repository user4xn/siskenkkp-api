<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerkKendaraan extends Model
{
    protected $table = 'merk_kendaraan';

    protected $fillable = [
        'id',
        'merk',
    ];

    public $timestamps = false;
}