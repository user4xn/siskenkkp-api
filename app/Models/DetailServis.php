<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailServis extends Model
{
    protected $table = 'detail_servis';

    protected $fillable = [
        'id',
        'idservis',
        'idjenisservis',
        'description',
    ];

    public $timestamps = false;

    public function detailJenis () {
        return $this->hasOne('App\Models\JenisServis', 'id', 'idjenisservis');
    }
}