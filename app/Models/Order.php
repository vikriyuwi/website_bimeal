<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Buyer;
use App\Models\OrderDetail;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory,HasUuids;
    protected $fillable = [
        'buyer_id',
        'status'
    ];
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }
    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
