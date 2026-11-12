<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $guarded = [];

    // Voucher bisa memiliki banyak frame khusus
    public function frames()
    {
        return $this->belongsToMany(Frame::class);
    }
}