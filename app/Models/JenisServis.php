<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisServis extends Model
{
    protected $table = 'jenis_servis';

    protected $fillable = [
        'id',
        'description',
    ];

    public $timestamps = false;
}