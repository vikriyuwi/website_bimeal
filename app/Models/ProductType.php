<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ProductType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name'
    ];
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
