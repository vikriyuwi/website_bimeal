<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Model\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model
{
    use HasFactory,HasUuids;
    protected $fillable = [
        'order_id',
        'bill',
        'status'
    ];
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
