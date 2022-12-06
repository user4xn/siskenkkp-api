<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerkKdrn extends Model
{
    protected $table = 'merk_kdrn';

    protected $fillable = [
        'id',
        'merk',
    ];
}