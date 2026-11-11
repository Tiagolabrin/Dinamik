<?php

namespace App\Http\Controllers;

use App\Models\Consultorio;
use App\Models\User;
use App\Models\Odontologo;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RoundlyConsulting\Appointments\Enums\ParticipantRole;
use RoundlyConsulting\Appointments\Enums\Status;
use RoundlyConsulting\Appointments\Facades\Appointments;
use RoundlyConsulting\Appointments\Models\Appointment;

class ReservaController extends Controller
{
    public function sacarTurno(): View{
        $odontologos = Odontologo::with('user')->get();

        return view('turnos.reservar', compact('odontologos'));
    }

    public function reservar(Request $request): RedirectResponse{
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Iniciá sesión para reservar un turno.');
        }
    
        $usuario = User::findOrFail(Auth::id());
    
        $data = $request->validate([
            'odontologo_id' => ['required', 'integer', 'exists:odontologos,id'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'horario' => ['required', 'date_format:H:i'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);
    
        $odontologo = Odontologo::with('user')->findOrFail($data['odontologo_id']);
    
        $timezone = config('appointments.timezone') ?: config('app.timezone', 'UTC');
    
        $fecha = CarbonImmutable::parse($data['fecha'], $timezone);
    
        $diaSemana = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            7 => 'domingo',
        ][$fecha->dayOfWeekIso];
    
        $duracion = max(1, (int) config('appointments.default_duration_minutes', 60));
    
        $inicio = CarbonImmutable::parse(
            $fecha->toDateString() . ' ' . $data['horario'],
            $timezone
        );
    
        $fin = $inicio->addMinutes($duracion);
    
        $hayVacaciones = $odontologo->vacaciones()
            ->whereDate('fecha_desde', '<=', $fecha->toDateString())
            ->whereDate('fecha_hasta', '>=', $fecha->toDateString())
            ->exists();
    
        if ($hayVacaciones) {
            throw ValidationException::withMessages([
                'fecha' => 'Ese odontólogo no atiende en la fecha seleccionada.',
            ]);
        }
    
        $dentroDeDisponibilidad = $odontologo->disponibilidades()
            ->where('dia_semana', $diaSemana)
            ->get()
            ->contains(function ($disponibilidad) use ($fecha, $inicio, $fin, $timezone) {
                $desde = CarbonImmutable::parse(
                    $fecha->toDateString() . ' ' . $disponibilidad->hora_inicio,
                    $timezone
                );
    
                $hasta = CarbonImmutable::parse(
                    $fecha->toDateString() . ' ' . $disponibilidad->hora_fin,
                    $timezone
                );
    
                return $inicio >= $desde && $fin <= $hasta;
            });
    
        if (! $dentroDeDisponibilidad || $inicio->isPast()) {
            throw ValidationException::withMessages([
                'horario' => 'Ese horario no está dentro de la disponibilidad del odontólogo.',
            ]);
        }
    
        $inicioUtc = $inicio->setTimezone('UTC');
        $finUtc = $fin->setTimezone('UTC');
    
        $ocupado = Appointment::query()
            ->whereHas('participants', function ($query) use ($odontologo) {
                $query->where('participant_type', $odontologo->getMorphClass())
                    ->where('participant_id', $odontologo->getKey());
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('starts_at', '<', $finUtc->toDateTimeString())
            ->where('ends_at', '>', $inicioUtc->toDateTimeString())
            ->exists();
    
        if ($ocupado) {
            throw ValidationException::withMessages([
                'horario' => 'Ese horario ya fue reservado. Elegí otro.',
            ]);
        }
    
        $appointment = DB::transaction(function () use (
            $data,
            $odontologo,
            $usuario,
            $inicio,
            $timezone,
            $duracion
        ) {
            $appointment = Appointments::schedule(
                'Consulta con ' . $odontologo->user->name . ' ' . $odontologo->user->apellido
            )
                ->startingAt($inicio->format('Y-m-d H:i'), timezone: $timezone)
                ->lasting($duracion)
                ->withParticipant($odontologo, ParticipantRole::Organiser)
                ->withParticipant($usuario, ParticipantRole::Attendee)
                ->withStatus(Status::Confirmed)
                ->preventConflicts()
                ->create();
    
            if (! empty($data['observaciones'])) {
                $appointment->update([
                    'description' => $data['observaciones'],
                ]);
            }
    
            return $appointment;
        });
    
        return redirect()->route('turno.confirmado', $appointment)
            ->with('success', 'Turno reservado correctamente.');
    }

    public function horarios(Request $request): JsonResponse{
        $data = $request->validate([
            'odontologo_id' => ['required', 'integer', 'exists:odontologos,id'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $odontologo = Odontologo::with([
            'disponibilidades',
            'vacaciones',
        ])->findOrFail($data['odontologo_id']);

        $timezone = config('appointments.timezone') ?: config('app.timezone', 'UTC');

        $fecha = CarbonImmutable::parse($data['fecha'], $timezone);

        $diaSemana = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            7 => 'domingo',
        ][$fecha->dayOfWeekIso];

        $hayVacaciones = $odontologo->vacaciones()
            ->whereDate('fecha_desde', '<=', $fecha->toDateString())
            ->whereDate('fecha_hasta', '>=', $fecha->toDateString())
            ->exists();

        if ($hayVacaciones) {
            return response()->json(['horarios' => []]);
        }

        $duracion = max(1, (int) config('appointments.default_duration_minutes', 60));

        $inicioDiaUtc = $fecha->startOfDay()->setTimezone('UTC');
        $finDiaUtc = $fecha->addDay()->startOfDay()->setTimezone('UTC');

        $turnos = Appointment::query()
            ->whereHas('participants', function ($query) use ($odontologo) {
                $query->where('participant_type', $odontologo->getMorphClass())
                    ->where('participant_id', $odontologo->getKey());
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('starts_at', '<', $finDiaUtc->toDateTimeString())
            ->where('ends_at', '>', $inicioDiaUtc->toDateTimeString())
            ->get(['starts_at', 'ends_at']);

        $horarios = [];

        foreach ($odontologo->disponibilidades->where('dia_semana', $diaSemana) as $disponibilidad) {
            $desde = CarbonImmutable::parse(
                $fecha->toDateString() . ' ' . $disponibilidad->hora_inicio,
                $timezone
            );

            $hasta = CarbonImmutable::parse(
                $fecha->toDateString() . ' ' . $disponibilidad->hora_fin,
                $timezone
            );

            for (
                $slot = $desde;
                $slot->addMinutes($duracion) <= $hasta;
                $slot = $slot->addMinutes($duracion)
            ) {
                $finSlot = $slot->addMinutes($duracion);

                if ($slot->isPast()) {
                    continue;
                }

                $ocupado = $turnos->contains(
                    fn($turno) => $turno->starts_at < $finSlot
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
    }

    public function misTurnos(): View{
        $usuario = User::findOrFail(Auth::id());

        $appointments = Appointment::query()
            ->whereHas('participants', function ($query) use ($usuario) {
                $query->where('participant_type', $usuario->getMorphClass())
                    ->where('participant_id', $usuario->getKey())
                    ->where('role', ParticipantRole::Attendee->value);
            })
            ->orderBy('starts_at')
            ->get();

        return view('turnos.mis_turnos', compact('appointments'));
    }

    public function agenda(): View{
        $usuario = Auth::user();
        $odontologo = $usuario->odontologo;
    
        abort_if($usuario->tipo_usuario !== 'odontologo' || ! $odontologo, 403);
    
        $appointments = Appointment::query()
            ->with('participants.participant')
            ->whereHas('participants', function ($query) use ($odontologo) {
                $query->where('participant_type', $odontologo->getMorphClass())
                    ->where('participant_id', $odontologo->getKey())
                    ->where('role', ParticipantRole::Organiser->value);
            })
            ->orderBy('starts_at')
            ->get();
    
        return view('odontologo.agenda', compact('appointments'));
    }
    
    public function confirmacion(Appointment $appointment): View
    {
        $usuario = Auth::user();
    
        abort_unless($usuario instanceof User, 403);

        abort_unless(
            $appointment->participants()
                ->where('participant_type', $usuario->getMorphClass())
                ->where('participant_id', $usuario->getKey())
                ->where('role', ParticipantRole::Attendee->value)
                ->exists(),
            403
        );
    
        $consultorio = Consultorio::first();
    
        return view('turnos.confirmacion', compact('appointment', 'consultorio'));
    }
}
?>