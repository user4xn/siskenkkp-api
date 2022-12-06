<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    protected $table = 'unitkerja';

    protected $fillable = [
        'id',
        'unitkerja',
    ];
}