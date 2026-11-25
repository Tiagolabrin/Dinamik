<?php

namespace App\Http\Controllers;

use App\Models\Consultorio;
use App\Models\User;
use App\Models\Odontologo;
use App\Notificaciones\TurnoCanceladoNotificacion;
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
    public function sacarTurno(): View
    {
        $odontologos = Odontologo::with('user')->get();

        return view('turnos.reservar', compact('odontologos'));
    }

    public function reservar(Request $request): RedirectResponse
    {
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
            $meta = $appointment->meta?->all() ?? [];

            $appointment->update([
                'meta' => array_merge($meta, [
                    'odontologo_nombre' => $odontologo->user->name,
                    'odontologo_apellido' => $odontologo->user->apellido,
                    'odontologo_matricula' => $odontologo->matricula,
                    'paciente_nombre' => $usuario->name,
                    'paciente_apellido' => $usuario->apellido,
                ]),
            ]);

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

    public function horarios(Request $request): JsonResponse
    {
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

    public function misTurnos(): View
    {
        $usuario = User::findOrFail(Auth::id());
        $query = Appointment::query()
            ->where('starts_at', '>=', $this->inicioVentanaListadoTurnos());

        if ($usuario->tipo_usuario === 'administrador') {
            // El administrador puede consultar y cancelar cualquier turno.
        } elseif ($usuario->tipo_usuario === 'odontologo') {
            $odontologo = $usuario->odontologo;
            abort_unless($odontologo, 403);

            $query->whereHas('participants', function ($query) use ($odontologo) {
                $query->where('participant_type', $odontologo->getMorphClass())
                    ->where('participant_id', $odontologo->getKey())
                    ->where('role', ParticipantRole::Organiser->value);
            });
        } else {
            $query->whereHas('participants', function ($query) use ($usuario) {
                $query->where('participant_type', $usuario->getMorphClass())
                    ->where('participant_id', $usuario->getKey())
                    ->where('role', ParticipantRole::Attendee->value);
            });
        }

        $appointments = $query->orderBy('starts_at')->get();

        return view('turnos.mis_turnos', compact('appointments'));
    }

    public function cancelarTurno(Appointment $appointment): RedirectResponse
    {
        $usuario = User::findOrFail(Auth::id());
        $esAdministrador = $usuario->tipo_usuario === 'administrador';
        $esPaciente = $appointment->participants()
            ->where('participant_type', $usuario->getMorphClass())
            ->where('participant_id', $usuario->getKey())
            ->where('role', ParticipantRole::Attendee->value)
            ->exists();
        $odontologo = $usuario->odontologo;
        $esOdontologo = $usuario->tipo_usuario === 'odontologo'
            && $odontologo
            && $appointment->participants()
                ->where('participant_type', $odontologo->getMorphClass())
                ->where('participant_id', $odontologo->getKey())
                ->where('role', ParticipantRole::Organiser->value)
                ->exists();

        abort_unless($esAdministrador || $esPaciente || $esOdontologo, 403);

        $rutaRedireccion = $esOdontologo && ! $esAdministrador ? 'odontologo.agenda' : 'mis_turnos';
        $resultado = DB::transaction(function () use ($appointment, $usuario): ?string {
            $turno = Appointment::query()
                ->lockForUpdate()
                ->findOrFail($appointment->getKey());

            if ($turno->starts_at->isPast()) {
                return 'iniciado';
            }

            if (! $turno->status->canTransitionTo(Status::Cancelled)) {
                return 'no_cancelable';
            }

            $paciente = $turno->participants()
                ->where('participant_type', (new User)->getMorphClass())
                ->where('role', ParticipantRole::Attendee->value)
                ->with('participant')
                ->firstOrFail()
                ->participant;
            $participanteOdontologo = $turno->participants()
                ->where('participant_type', (new Odontologo)->getMorphClass())
                ->where('role', ParticipantRole::Organiser->value)
                ->with('participant.user')
                ->firstOrFail()
                ->participant;

            if (! $paciente instanceof User || ! $participanteOdontologo instanceof Odontologo) {
                throw new \RuntimeException('No se pudieron identificar los participantes del turno cancelado.');
            }

            $odontologo = $participanteOdontologo->user;
            if (! $odontologo instanceof User) {
                throw new \RuntimeException('No se pudo identificar la cuenta del odontólogo del turno cancelado.');
            }

            $fechaTurno = $turno->starts_at
                ->setTimezone(config('app.timezone'))
                ->format('d/m/Y H:i');
            $canceladoPor = trim($usuario->name.' '.$usuario->apellido);

            Appointments::for($turno)->cancel();

            $paciente->notify(new TurnoCanceladoNotificacion(
                $turno->name,
                $fechaTurno,
                $canceladoPor,
            ));
            $odontologo->notify(new TurnoCanceladoNotificacion(
                $turno->name,
                $fechaTurno,
                $canceladoPor,
            ));

            return null;
        });

        if ($resultado === 'iniciado') {
            return redirect()->route($rutaRedireccion)
                ->with('error', 'No se puede cancelar un turno que ya comenzó.');
        }

        if ($resultado === 'no_cancelable') {
            return redirect()->route($rutaRedireccion)
                ->with('error', 'Este turno ya fue cancelado o no se puede cancelar.');
        }

        return redirect()->route($rutaRedireccion)
            ->with('success', 'El turno se canceló correctamente.');
    }

    public function agenda(): View
    {
        $usuario = Auth::user();
        $odontologo = $usuario->odontologo;

        abort_if($usuario->tipo_usuario !== 'odontologo' || ! $odontologo, 403);

        $appointments = Appointment::query()
            ->with('participants.participant')
            ->where('starts_at', '>=', $this->inicioVentanaListadoTurnos())
            ->whereHas('participants', function ($query) use ($odontologo) {
                $query->where('participant_type', $odontologo->getMorphClass())
                    ->where('participant_id', $odontologo->getKey())
                    ->where('role', ParticipantRole::Organiser->value);
            })
            ->orderBy('starts_at')
            ->get();

        return view('odontologo.agenda', compact('appointments'));
    }

    private function inicioVentanaListadoTurnos(): string
    {
        return CarbonImmutable::now(config('app.timezone'))
            ->subDays(3)
            ->startOfDay()
            ->utc()
            ->toDateTimeString();
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
