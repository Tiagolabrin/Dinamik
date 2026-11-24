<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

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
