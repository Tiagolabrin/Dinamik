<?php

namespace App\Http\Controllers;

use App\Notificaciones\TurnoCanceladoNotificacion;
use App\Notificaciones\TurnoReservadoNotificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function recientes(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = $user->notificaciones()
            ->whereIn('type', $this->tiposDeNotificacion());

        $notificaciones = (clone $query)
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn ($notificacion): array => [
                'id' => $notificacion->id,
                'title' => $notificacion->data['title'],
                'message' => $notificacion->data['message'],
                'read' => $notificacion->read_at !== null,
                'created_at' => $notificacion->created_at->toIso8601String(),
            ]);

        return response()->json([
            'notifications' => $notificaciones,
            'unread_count' => (clone $query)->whereNull('read_at')->count(),
        ]);
    }

    public function noLeidas(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notificaciones()
            ->whereNull('read_at')
            ->whereIn('type', $this->tiposDeNotificacion())
            ->oldest()
            ->limit(50)
            ->get()
            ->map(fn ($notification): array => [
                'id' => $notification->id,
                'title' => $notification->data['title'],
                'message' => $notification->data['message'],
            ]);

        return response()->json(['notifications' => $notifications]);
    }

    public function marcarLeidas(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:50'],
            'ids.*' => ['required', 'uuid'],
        ]);

        $request->user()
            ->notificaciones()
            ->whereNull('read_at')
            ->whereIn('type', $this->tiposDeNotificacion())
            ->whereIn('id', $validated['ids'])
            ->update(['read_at' => now()]);

        return response()->json([], 204);
    }

    private function tiposDeNotificacion(): array
    {
        return [
            TurnoCanceladoNotificacion::class,
            TurnoReservadoNotificacion::class,
        ];
    }
}
