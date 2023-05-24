<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Buyer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Topup extends Model
{
    use HasFactory,HasUuids;
    protected $fillable = [
        'buyer_id',
        'debt',
        'status'
    ];
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }
}
