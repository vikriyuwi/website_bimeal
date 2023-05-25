<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OrderDetail extends Model
{
    use HasFactory,HasUuids;
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity'
    ];
    protected $hidden = [
        'order_id',
    ];
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
