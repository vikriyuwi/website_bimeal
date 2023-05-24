<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Withdraw extends Model
{
    use HasFactory,HasUuids;
    protected $fillable = [
        'merchant_id',
        'credit',
        'status'
    ];
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
