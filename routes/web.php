<?php

use App\Models\Odontologo;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use RoundlyConsulting\Appointments\Enums\ParticipantRole;
use RoundlyConsulting\Appointments\Facades\Appointments;
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

Route::post('/reserva', function (Request $request) {
    $usuario = Auth::user();

    if (! $usuario) {
        return redirect()->route('login')->with('error', 'Iniciá sesión para reservar un turno.');
    }

    $data = $request->validate([
        'odontologo_id' => ['required', 'integer', 'exists:odontologos,id'],
        'fecha' => ['required', 'date', 'after_or_equal:today'],
        'horario' => ['required', 'date_format:H:i'],
        'observaciones' => ['nullable', 'string', 'max:1000'],
    ]);

    $odontologo = Odontologo::with('user')->findOrFail($data['odontologo_id']);
    $timezone = config('appointments.timezone') ?: config('app.timezone', 'UTC');
    $fecha = CarbonImmutable::parse($data['fecha'], $timezone);
    $diasSemana = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
        7 => 'domingo',
    ];
    $diaSemana = $diasSemana[$fecha->dayOfWeekIso] ?? null;

    if (! $diaSemana) {
        throw ValidationException::withMessages([
            'fecha' => 'La fecha seleccionada es inválida.',
        ]);
    }

    $hayVacaciones = $odontologo->vacaciones()
        ->whereDate('fecha_desde', '<=', $fecha->toDateString())
        ->whereDate('fecha_hasta', '>=', $fecha->toDateString())
        ->exists();

    if ($hayVacaciones) {
        throw ValidationException::withMessages([
            'fecha' => 'Ese odontólogo no atiende en la fecha seleccionada.',
        ]);
    }

    $disponibilidad = $odontologo->disponibilidades()
        ->where('dia_semana', $diaSemana)
        ->get()
        ->first(function ($disponibilidad) use ($fecha, $timezone, $data) {
            $inicio = CarbonImmutable::parse($fecha->toDateString().' '.$disponibilidad->hora_inicio, $timezone);
            $fin = CarbonImmutable::parse($fecha->toDateString().' '.$disponibilidad->hora_fin, $timezone);
            $turnoInicio = CarbonImmutable::parse($fecha->toDateString().' '.$data['horario'], $timezone);
            $turnoFin = $turnoInicio->addMinutes(30);

            return $turnoInicio >= $inicio && $turnoFin <= $fin;
        });

    if (! $disponibilidad) {
        throw ValidationException::withMessages([
            'horario' => 'Ese horario no está disponible para el odontólogo elegido.',
        ]);
    }

    $inicio = CarbonImmutable::parse($fecha->toDateString().' '.$data['horario'], $timezone);
    $fin = $inicio->copy()->addMinutes(30);

    $haySolapamiento = Appointment::query()
        ->whereHas('participants', function ($query) use ($odontologo) {
            $query->where('participant_type', $odontologo->getMorphClass())
                ->where('participant_id', $odontologo->getKey());
        })
        ->whereIn('status', ['pending', 'confirmed'])
        ->where('starts_at', '<', $fin->setTimezone('UTC')->toDateTimeString())
        ->where('ends_at', '>', $inicio->setTimezone('UTC')->toDateTimeString())
        ->exists();

    if ($haySolapamiento) {
        throw ValidationException::withMessages([
            'horario' => 'Ese horario ya fue reservado. Elegí otro.',
        ]);
    }

    $appointment = DB::transaction(function () use ($odontologo, $usuario, $inicio, $timezone, $data) {
        $appointment = Appointments::schedule(
            'Consulta con '.$odontologo->user->name.' '.$odontologo->user->apellido
        )
            ->startingAt($inicio->format('Y-m-d H:i'), timezone: $timezone)
            ->lasting(30)
            ->withParticipant($odontologo, ParticipantRole::Organiser)
            ->withParticipant($usuario, ParticipantRole::Attendee)
            ->preventConflicts()
            ->create();

        if (! empty($data['observaciones'])) {
            $appointment->update(['description' => $data['observaciones']]);
        }

        return $appointment;
    });

    return redirect()->route('turno.confirmado', $appointment)->with('success', 'Turno reservado correctamente.');
})->name('reserva.store');

Route::get('/reserva/horarios', function (Request $request) {
    $datos = $request->validate([
        'odontologo_id' => ['required', 'integer', 'exists:odontologos,id'],
        'fecha' => ['required', 'date', 'after_or_equal:today'],
    ]);

    $odontologo = Odontologo::with(['disponibilidades', 'vacaciones'])->findOrFail($datos['odontologo_id']);
    $timezone = config('appointments.timezone') ?: config('app.timezone', 'UTC');
    $fecha = CarbonImmutable::parse($datos['fecha'], $timezone);
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

    if ($odontologo->vacaciones()->whereDate('fecha_desde', '<=', $fecha->toDateString())->whereDate('fecha_hasta', '>=', $fecha->toDateString())->exists()) {
        return response()->json(['horarios' => []]);
    }

    $inicioDiaUtc = $fecha->startOfDay()->setTimezone('UTC');
    $finDiaUtc = $fecha->copy()->addDay()->startOfDay()->setTimezone('UTC');
    $turnos = Appointment::query()
        ->whereHas('participants', function ($query) use ($odontologo) {
            $query->where('participant_type', $odontologo->getMorphClass())
                ->where('participant_id', $odontologo->getKey());
        })
        ->whereIn('status', ['pending', 'confirmed'])
        ->where('starts_at', '<', $finDiaUtc->toDateTimeString())
        ->where('ends_at', '>', $inicioDiaUtc->toDateTimeString())
        ->get(['starts_at', 'ends_at']);

    $duracionMinutos = 30;
    $horarios = [];

    foreach ($odontologo->disponibilidades->where('dia_semana', $diaSemana) as $disponibilidad) {
        $desde = CarbonImmutable::parse($fecha->toDateString().' '.$disponibilidad->hora_inicio, $timezone);
        $hasta = CarbonImmutable::parse($fecha->toDateString().' '.$disponibilidad->hora_fin, $timezone);

        for ($slot = $desde; $slot->copy()->addMinutes($duracionMinutos) <= $hasta; $slot = $slot->copy()->addMinutes($duracionMinutos)) {
            $finSlot = $slot->copy()->addMinutes($duracionMinutos);

            if ($slot->isPast()) {
                continue;
            }

            $ocupado = $turnos->contains(fn ($turno) => $turno->starts_at < $finSlot && $turno->ends_at > $slot);

            if (! $ocupado) {
                $horarios[] = $slot->format('H:i');
            }
        }
    }

    return response()->json(['horarios' => array_values(array_unique($horarios))]);
})->name('reserva.horarios');

Route::get('/mis-turnos', function () {
    $usuario = Auth::user();

    if (! $usuario) {
        return redirect()->route('login');
    }

    $appointments = Appointment::query()
        ->whereHas('participants', function ($query) use ($usuario) {
            $query->where('participant_type', $usuario->getMorphClass())
                ->where('participant_id', $usuario->getKey());
        })
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
    $usuario = Auth::user();

    abort_unless(
        $usuario && $appointment->participants()->where('participant_type', $usuario->getMorphClass())->where('participant_id', $usuario->getKey())->exists(),
        403
    );

    $consultorio = \App\Models\Consultorio::first();

    return view('turnos.confirmacion', compact('appointment', 'consultorio'));
})->name('turno.confirmado');
