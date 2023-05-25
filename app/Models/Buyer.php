<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Account;
use App\Models\Topup;
use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buyer extends Model
{
    use HasFactory,HasUuids;
    protected $fillable = [
        'username',
        'password',
        'email',
        'phone',
        'verified_at',
        'token',
        'name',
        'group',
        'group_id',
    ];
    public function topups(): HasMany
    {
        return $this->hasMany(Topup::class);
    }
    public function order(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
