<?php

namespace App\Http\Controllers;

use App\Notifications\TurnoCanceladoNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function noLeidas(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->where('type', TurnoCanceladoNotification::class)
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
            ->unreadNotifications()
            ->where('type', TurnoCanceladoNotification::class)
            ->whereIn('id', $validated['ids'])
            ->update(['read_at' => now()]);

        return response()->json([], 204);
    }
}
