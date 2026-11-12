<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BoothSession extends Model
{
    use HasUuids; // Penting: Mengaktifkan Auto-UUID

    protected $guarded = [];

    public function frame()
    {
        return $this->belongsTo(Frame::class);
    }

    public function media()
    {
        return $this->hasMany(SessionMedia::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}