<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class TurnoCanceladoNotification extends Notification
{
    public function __construct(
        private readonly string $nombreTurno,
        private readonly string $fechaTurno,
        private readonly string $canceladoPor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Turno cancelado',
            'message' => "El turno «{$this->nombreTurno}», programado para {$this->fechaTurno}, fue cancelado por {$this->canceladoPor}.",
        ];
    }
}
