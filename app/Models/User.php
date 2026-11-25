<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class User extends Authenticatable
{
    use Notifiable;

    protected $guarded = [];

    public function notificaciones(): MorphMany
    {
        return $this->morphMany(Notificacion::class, 'notifiable')->latest();
    }

    public function routeNotificationForDatabase(?Notification $notification = null): MorphMany
    {
        return $this->notificaciones();
    }

    public function paciente()
    {
        return $this->hasOne(Paciente::class);
    }

    public function odontologo()
    {
        return $this->hasOne(Odontologo::class);
    }
}
