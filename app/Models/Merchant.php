<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Order;
use App\Models\Withdraw;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
class Merchant extends Authenticatable implements JWTSubject
{
    use HasFactory,HasUuids,Notifiable;
    protected $fillable = [
        'username',
        'password',
        'email',
        'phone',
        'verified_at',
        'remember_token',
        'name',
        'location_number',
        'time_open',
        'time_close'
    ];
    protected $hidden = [
        'password',
        'remember_token'
    ];
    public function withdraws(): HasMany
    {
        return $this->hasMany(Withdraw::class);
    }
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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
