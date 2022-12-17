<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servis extends Model
{
    protected $table = 'servis';

    protected $fillable = [
        'id',
        'idkdrn',
        'tgl',
        'jaraktempuh',
        'nmbengkel',
    ];
}