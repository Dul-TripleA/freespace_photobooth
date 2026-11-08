<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SessionMedia extends Model
{
    use HasUuids; // Aktifkan UUID

    // Mengizinkan semua kolom untuk diisi (Mass Assignment)
    protected $guarded = [];
}
