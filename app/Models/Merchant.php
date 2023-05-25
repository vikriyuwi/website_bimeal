<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Account;
use App\Models\Withdraw;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Merchant extends Model
{
    use HasFactory,HasUuids;
    protected $fillable = [
        'username',
        'password',
        'email',
        'phone',
        'role',
        'verified_at',
        'token',
        'name',
        'location_number',
        'time_open',
        'time_close'
    ];
    public function withdraws(): HasMany
    {
        return $this->hasMany(Withdraw::class);
    }
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
