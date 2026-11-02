<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Odontologo extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function disponibilidades()
    {
        return $this->hasMany(Disponibilidad::class);
    }
    public function vacaciones()
    {
        return $this->hasMany(Vacaciones::class);
    }
}
