<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory,HasUuids;

    protected $table = 'account';

    protected $fillable = [
        'username',
        'password',
        'email',
        'phone',
        'role',
    ];

    protected $hidden = [
        'password'
    ];
}
