<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Buyer;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Account extends Model
{
    use HasFactory,HasUuids;
    protected $fillable = [
        'username',
        'password',
        'email',
        'phone',
        'role',
        'verified_at',
        'token'
    ];
    protected $hidden = [
        'password'
    ];
    public function buyer(): HasOne
    {
        return $this->hasOne(Buyer::class);
    }
    public function merchant(): HasOne
    {
        return $this->hasOne(Merchant::class);
    }
}
