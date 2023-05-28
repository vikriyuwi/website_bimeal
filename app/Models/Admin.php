<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Topup;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory,HasUuids,Notifiable;
    protected $fillable = [
        'username',
        'password',
        'email',
        'phone',
        'verified_at',
        'remember_token',
        'name'
    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
    public function topups(): HasMany {
        return $this->hasMany(Topup::class);
    }
    public function getJWTIdentifier() {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    } 
}
