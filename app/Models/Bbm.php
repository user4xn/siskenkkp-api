<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bbm extends Model
{
    protected $table = 'bbm';

    protected $fillable = [
        'id',
        'iddetailpinjam',
        'kmsebelum',
        'kmsesudah',
        'kmspt',
        'sisakm',
        'jmlliter',
        'harga',
        'total',
    ];
}