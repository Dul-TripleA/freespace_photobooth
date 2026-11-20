<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashlessCode extends Model
{
    protected $fillable = [
        'code',
        'is_used',
        'used_at',
        'booth_session_id',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];
}