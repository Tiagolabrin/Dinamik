<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $guarded = [];

    public function paciente()
    {
        return $this->hasOne(Paciente::class);
    }

    public function odontologo()
    {
        return $this->hasOne(Odontologo::class);
    }
}
