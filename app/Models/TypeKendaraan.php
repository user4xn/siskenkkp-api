<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeKendaraan extends Model
{
    protected $table = 'typekendaraan';

    protected $fillable = [
        'id',
        'typekdrn',
    ];
}