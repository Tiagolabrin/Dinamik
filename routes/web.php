<?php

use App\Models\Odontologo;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use RoundlyConsulting\Appointments\Models\Appointment;
use RoundlyConsulting\Appointments\Models\Participant;

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

Route::post('/reserva', function (Request $request) {
    $data = $request->validate([
        'odontologo_id' => ['required', 'integer', 'exists:odontologos,id'],
        'fecha' => ['required', 'date', 'after_or_equal:today'],
        'horario' => ['required', 'date_format:H:i'],
        'observaciones' => ['nullable', 'string', 'max:1000'],
    ]);

    $odontologo = Odontologo::with(['disponibilidades', 'vacaciones'])
        ->findOrFail($data['odontologo_id']);

    $fecha = CarbonImmutable::parse($data['fecha']);
    $diaSemana = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
        7 => 'domingo',
    ][$fecha->dayOfWeekIso] ?? null;

    abort_if(! $diaSemana, 422, 'La fecha no es válida para reservar.');

    $estaDeVacaciones = $odontologo->vacaciones()
        ->whereDate('fecha_desde', '<=', $fecha->toDateString())
        ->whereDate('fecha_hasta', '>=', $fecha->toDateString())
        ->exists();

    abort_if($estaDeVacaciones, 422, 'Ese odontólogo no atiende en la fecha seleccionada.');

    $disponibilidad = $odontologo->disponibilidades()
        ->where('dia_semana', $diaSemana)
        ->first();

    abort_if(! $disponibilidad, 422, 'Ese odontólogo no tiene disponibilidad para ese día.');

    $inicio = CarbonImmutable::parse($fecha->toDateString().' '.$data['horario']);
    $fin = $inicio->copy()->addMinutes(30);

    $solapaConOtroTurno = Appointment::query()
        ->whereHas('participants', function ($query) use ($odontologo) {
            $query->where('participant_type', $odontologo->getMorphClass())
                ->where('participant_id', $odontologo->getKey());
        })
        ->whereNotIn('status', ['cancelled', 'declined'])
        ->where('starts_at', '<', $fin)
        ->where('ends_at', '>', $inicio)
        ->exists();

    abort_if($solapaConOtroTurno, 422, 'Ese horario ya no está disponible.');

    $usuario = Auth::user();
    abort_if(! $usuario, 401, 'Debés iniciar sesión para reservar.');

    $appointment = Appointment::create([
        'name' => 'Consulta con '.$odontologo->user->name.' '.$odontologo->user->apellido,
        'status' => 'pending',
        'description' => $data['observaciones'] ?? null,
        'starts_at' => $inicio->toDateTimeString(),
        'ends_at' => $fin->toDateTimeString(),
        'duration_minutes' => 30,
        'timezone' => config('app.timezone', 'UTC'),
    ]);

    $appointment->participants()->create([
        'participant_id' => $odontologo->getKey(),
        'participant_type' => $odontologo->getMorphClass(),
        'role' => 'organiser',
    ]);

    $appointment->participants()->create([
        'participant_id' => $usuario->getKey(),
        'participant_type' => $usuario->getMorphClass(),
        'role' => 'attendee',
    ]);

    return redirect()->route('mis_turnos')->with('success', 'Turno reservado correctamente.');
})->name('reserva.store');

Route::get('/reserva/horarios', function (Request $request) {
    $datos = $request->validate([
        'odontologo_id' => ['required', 'integer', 'exists:odontologos,id'],
        'fecha' => ['required', 'date', 'after_or_equal:today'],
    ]);

    $odontologo = Odontologo::with(['disponibilidades', 'vacaciones'])
        ->findOrFail($datos['odontologo_id']);

    $fecha = CarbonImmutable::parse($datos['fecha']);
    $diaSemana = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
        7 => 'domingo',
    ][$fecha->dayOfWeekIso] ?? null;

    if (! $diaSemana) {
        return response()->json(['horarios' => []]);
    }

    $hayVacaciones = $odontologo->vacaciones()
        ->whereDate('fecha_desde', '<=', $fecha->toDateString())
        ->whereDate('fecha_hasta', '>=', $fecha->toDateString())
        ->exists();

    if ($hayVacaciones) {
        return response()->json(['horarios' => []]);
    }

    $disponibilidades = $odontologo->disponibilidades()
        ->where('dia_semana', $diaSemana)
        ->get();

    $turnos = Appointment::query()
        ->whereHas('participants', function ($query) use ($odontologo) {
            $query->where('participant_type', $odontologo->getMorphClass())
                ->where('participant_id', $odontologo->getKey());
        })
        ->whereNotIn('status', ['cancelled', 'declined'])
        ->where('starts_at', '<', $fecha->copy()->endOfDay())
        ->where('ends_at', '>', $fecha->copy()->startOfDay())
        ->get(['starts_at', 'ends_at']);

    $duracionMinutos = 30;
    $horarios = [];

    foreach ($disponibilidades as $disponibilidad) {
        $inicio = $fecha->setTimeFromTimeString($disponibilidad->hora_inicio);
        $fin = $fecha->setTimeFromTimeString($disponibilidad->hora_fin);

        for ($slot = $inicio; $slot->copy()->addMinutes($duracionMinutos) <= $fin; $slot = $slot->copy()->addMinutes($duracionMinutos)) {
            $finSlot = $slot->copy()->addMinutes($duracionMinutos);

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
