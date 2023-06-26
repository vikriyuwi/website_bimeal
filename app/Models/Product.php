<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Merchant;
use App\Models\ProductType;
use App\Models\OrderDetail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory,HasUuids;
    protected $fillable = [
        'merchant_id',
        'product_type_id',
        'image',
        'name',
        'price',
        'is_available',
        'is_active'
    ];
    protected $hidden = [
        'merchant_id',
        'is_available',
        'is_active'
    ];
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }
    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
}
