<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disponibilidad extends Model
{
    protected $guarded = [];
    protected $table= "disponibilidades";

    public function odontologo()
    {
        return $this->belongsTo(Odontologo::class);
    }
}
