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

    public function detailServis () {
        return $this->hasMany('App\Models\DetailServis', 'idservis', 'id');
    }
}