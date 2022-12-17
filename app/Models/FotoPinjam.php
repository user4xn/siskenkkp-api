<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoPinjam extends Model
{
    protected $table = 'foto_pinjam';

    protected $fillable = [
        'id',
        'reference_id',
        'type',
        'urlfoto',
    ];
}