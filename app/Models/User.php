<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;
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
