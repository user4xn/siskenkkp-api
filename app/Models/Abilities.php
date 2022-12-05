<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abilities extends Model
{
    protected $table = 'abilities';

    protected $fillable = [
        'id',
        'ability_name'
    ];
}