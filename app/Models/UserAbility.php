<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAbility extends Model
{
    protected $table = 'user_ability';

    protected $fillable = [
        'id',
        'user_id',
        'ability_id',
        'ability_menu_id',
        'created_at',
        'updated_at'
    ];

    public function abilities() {
        return $this->hasOne('App\Models\Abilities', 'id', 'ability_id');
    }

    public function abilityMenu() {
        return $this->hasOne('App\Models\AbilityMenu', 'id', 'ability_menu_id');
    }
}