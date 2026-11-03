<?php

use App\Models\Odontologo;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use RoundlyConsulting\Appointments\Models\Appointment;

Route::view('/', 'inicio')->name('inicio');

Route::view('/login', 'login')->name('login');
Route::view('/registro', 'registro')->name('registro');

Route::get('/perfil', function () {
    $user = Auth::user()
        ?? User::with([
            'paciente',
            'odontologo.disponibilidades',
            'odontologo.vacaciones',
        ])->first();

    abort_if(! $user, 404);

    return view('perfil', compact('user'));
})->name('perfil');

Route::get('/perfil/editar', function () {
    $user = Auth::user()
        ?? User::with([
            'paciente',
            'odontologo.disponibilidades',
            'odontologo.vacaciones',
        ])->first();

    abort_if(! $user, 404);

    return view('perfil_editar', compact('user'));
})->name('perfil_editar');

Route::get('/reserva', function () {
    $odontologos = Odontologo::with('user')->get();

    return view('turnos.reservar', compact('odontologos'));
})->name('reserva');

Route::get('/reserva/horarios', function (Request $request) {
    $datos = $request->validate([
        'odontologo_id' => ['required', 'integer', 'exists:odontologos,id'],
        'fecha' => ['required', 'date', 'after_or_equal:today'],
    ]);

    $odontologo = Odontologo::with(['disponibilidades', 'vacaciones'])
        ->findOrFail($datos['odontologo_id']);

    $fecha = CarbonImmutable::parse($datos['fecha']);
    $fechaTexto = $fecha->toDateString();

    $estaDeVacaciones = $odontologo->vacaciones()
        ->whereDate('fecha_desde', '<=', $fechaTexto)
        ->whereDate('fecha_hasta', '>=', $fechaTexto)
        ->exists();

    if ($estaDeVacaciones) {
        return response()->json(['horarios' => []]);
    }

    $diaSemana = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
        7 => 'domingo',
    ][$fecha->dayOfWeekIso];

    $disponibilidades = $odontologo->disponibilidades
        ->where('dia_semana', $diaSemana);

    $turnos = Appointment::query()
        ->whereHas('participants', function ($query) use ($odontologo) {
            $query->where('participant_type', $odontologo->getMorphClass())
                ->where('participant_id', $odontologo->getKey());
        })
        ->whereNotIn('status', ['cancelled', 'declined'])
        ->where('starts_at', '<', $fecha->addDay()->startOfDay())
        ->where('ends_at', '>', $fecha->startOfDay())
        ->get(['starts_at', 'ends_at']);

    $duracionMinutos = max(1, (int) config('appointments.default_duration_minutes', 60));
    $horarios = [];

    foreach ($disponibilidades as $disponibilidad) {
        $inicio = $fecha->setTimeFromTimeString($disponibilidad->hora_inicio);
        $fin = $fecha->setTimeFromTimeString($disponibilidad->hora_fin);

        for ($slot = $inicio; $slot->addMinutes($duracionMinutos) <= $fin; $slot = $slot->addMinutes($duracionMinutos)) {
            $finSlot = $slot->addMinutes($duracionMinutos);

            if ($slot->isPast()) {
                continue;
            }

            $ocupado = $turnos->contains(
                fn ($turno) => $turno->starts_at < $finSlot
                    && $turno->ends_at > $slot
            );

            if (! $ocupado) {
                $horarios[] = $slot->format('H:i');
            }
        }
    }

    return response()->json([
        'horarios' => array_values(array_unique($horarios)),
    ]);
})->name('reserva.horarios');

Route::get('/mis-turnos', function () {
    $appointments = Appointment::query()
        ->orderBy('starts_at')
        ->get();

    return view('turnos.mis_turnos', compact('appointments'));
})->name('mis_turnos');

Route::get('/agenda', function () {
    $appointments = Appointment::query()
        ->whereBetween('starts_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])
        ->orderBy('starts_at')
        ->get();

    return view('agenda', compact('appointments'));
})->name('agenda');

Route::get('/odontologo/agenda', function () {
    $appointments = Appointment::query()
        ->orderBy('starts_at')
        ->get();

    return view('odontologo.agenda', compact('appointments'));
})->name('odontologo.agenda');

Route::get('/turno/confirmado/{appointment}', function (Appointment $appointment) {
    $consultorio = \App\Models\Consultorio::first();

    return view('turnos.confirmacion', compact('appointment', 'consultorio'));
})->name('turno.confirmado');
