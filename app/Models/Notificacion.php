<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class Notificacion extends DatabaseNotification
{
    protected $table = 'notificaciones';
}
