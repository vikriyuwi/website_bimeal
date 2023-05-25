<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model
{
    use HasFactory,HasUuids;
    protected $fillable = [
        'order_id',
        'bill',
        'status'
    ];
    protected $hidden = [
        'order_id'
    ];
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
