<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeKendaraan extends Model
{
    protected $table = 'type_kendaraan';

    protected $fillable = [
        'id',
        'type',
    ];

    public $timestamps = false;
}