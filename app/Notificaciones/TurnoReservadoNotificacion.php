<?php

namespace App\Notificaciones;

use Illuminate\Notifications\Notification;

class TurnoReservadoNotificacion extends Notification
{
    public function __construct(
        private readonly string $nombreTurno,
        private readonly string $fechaTurno,
        private readonly string $nombrePaciente,
        private readonly string $nombreOdontologo,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Turno reservado',
            'message' => "Se reservó el turno «{$this->nombreTurno}» para {$this->fechaTurno}. Paciente: {$this->nombrePaciente}. Odontólogo: {$this->nombreOdontologo}.",
        ];
    }
}
