<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbilityMenu extends Model
{
    protected $table = 'ability_menu';

    protected $fillable = [
        'id',
        'parent_id',
        'name',
        'created_at',
        'updated_at'
    ];

    public function parentMenu() {
        return $this->hasOne('App\Models\AbilityMenu', 'id', 'parent_id')->where('parent_id', '=', 0)->select('id', 'parent_id', 'name');
    }
    
    public function childMenu() {
        return $this->hasMany('App\Models\AbilityMenu', 'parent_id', 'id')->select('id', 'parent_id', 'name');
    }
}