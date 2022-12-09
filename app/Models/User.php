<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\User;
use App\Models\UserAbility;
use App\Models\UserPegawai;
use App\Models\Pegawai;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'role_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims () {
        return [];
    } 

    public function userPegawai () {
        return $this->hasOne('App\Models\UserPegawai', 'userid', 'id');
    }

    public function roleDetail() {
        return $this->hasOne('App\Models\Role', 'id' , 'role_id');
    }

    public function deleteAll ($user_id, $nip) {
        DB::beginTransaction();
        try {
            UserAbility::where('user_id', $user_id)->delete();
            Pegawai::where('nip', $nip)->delete();
            UserPegawai::where(['userid' => $user_id, 'nip' => $nip])->delete();
            User::where('id', $user_id)->delete();
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollback();
            return false;
        }
    }
}
