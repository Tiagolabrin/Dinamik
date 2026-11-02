<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacaciones extends Model
{
    protected $guarded = [];
    public function odontologo()
    {
        return $this->belongsTo(Odontologo::class);
    }
}
