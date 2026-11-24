<?php

namespace Database\Seeders;

use App\Models\Consultorio;
use App\Models\Odontologo;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RoundlyConsulting\Appointments\Enums\ParticipantRole;
use RoundlyConsulting\Appointments\Enums\Status;
use RoundlyConsulting\Appointments\Facades\Appointments;
use RoundlyConsulting\Appointments\Models\Appointment;

class DatosDePruebaSeeder extends Seeder
{
    public function run(): void
    {
        $pacientePassword = config('app.demo_accounts.paciente_password');
        $odontologoPassword = config('app.demo_accounts.odontologo_password');

        if (! is_string($pacientePassword) || strlen($pacientePassword) < 8
            || ! is_string($odontologoPassword) || strlen($odontologoPassword) < 8) {
            throw new \RuntimeException(
                'Configurá DEMO_PACIENTE_PASSWORD y DEMO_ODONTOLOGO_PASSWORD en .env '
                .'con contraseñas de al menos 8 caracteres para cargar los datos de prueba.'
            );
        }

        $pacienteUser = $this->demoUser(
            nombre: 'Paciente',
            apellido: 'Demostración',
            email: 'paciente.demo@example.test',
            password: $pacientePassword,
            rol: 'paciente',
        );
        $paciente = $pacienteUser->paciente()->firstOrCreate([], [
            'obra_social' => 'ISSN',
            'fecha_nacimiento' => CarbonImmutable::now()->subYears(30)->toDateString(),
            'direccion' => 'Dirección de prueba 123, Neuquén',
        ]);

        $odontologoUser = $this->demoUser(
            nombre: 'Odontóloga',
            apellido: 'Demostración',
            email: 'odontologa.demo@example.test',
            password: $odontologoPassword,
            rol: 'odontologo',
        );
        $odontologo = $odontologoUser->odontologo()->firstOrCreate([], [
            'matricula' => 'MAT-DEMO-001',
        ]);

        foreach ([
            'lunes',
            'martes',
            'miercoles',
            'jueves',
            'viernes',
        ] as $diaSemana) {
            $odontologo->disponibilidades()->updateOrCreate(
                ['dia_semana' => $diaSemana],
                ['hora_inicio' => '09:00', 'hora_fin' => '12:00'],
            );
        }

        $timezone = config('appointments.timezone') ?: config('app.timezone', 'UTC');
        $hoy = CarbonImmutable::now($timezone);
        $proxLunes = $hoy->next(Carbon::MONDAY);
        $arrancaVacaciones = $hoy->addWeeks(2)->next(Carbon::MONDAY);

        $odontologo->vacaciones()->updateOrCreate([], [
            'fecha_desde' => $arrancaVacaciones->toDateString(),
            'fecha_hasta' => $arrancaVacaciones->addDays(4)->toDateString(),
        ]);

        Consultorio::firstOrCreate([], [
            'direccion' => 'Dirección de prueba 123, Neuquén',
            'telefono' => '299 000 0000',
            'email' => 'contacto@consultorio-demo.test',
        ]);

        $this->createDemoAppointmentIfMissing($odontologo, $pacienteUser, $proxLunes, $timezone);
    }

    private function demoUser(
        string $nombre,
        string $apellido,
        string $email,
        string $password,
        string $rol,
    ): User {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $nombre,
                'apellido' => $apellido,
                'password' => Hash::make($password),
                'tipo_usuario' => $rol,
            ],
        );

        if ($user->tipo_usuario !== $rol) {
            throw new \RuntimeException("El email {$email} ya pertenece a un usuario con otro perfil.");
        }

        return $user;
    }

    private function createDemoAppointmentIfMissing(
        Odontologo $odontologo,
        User $paciente,
        CarbonImmutable $date,
        string $timezone,
    ): void {
        $existeTurno = Appointment::query()
            ->where('name', 'Consulta de demostración')
            ->where('starts_at', '>=', now('UTC'))
            ->where('status', Status::Confirmed->value)
            ->whereHas('participants', function ($query) use ($odontologo) {
                $query->where('participant_type', $odontologo->getMorphClass())
                    ->where('participant_id', $odontologo->getKey())
                    ->where('role', ParticipantRole::Organiser->value);
            })
            ->whereHas('participants', function ($query) use ($paciente) {
                $query->where('participant_type', $paciente->getMorphClass())
                    ->where('participant_id', $paciente->getKey())
                    ->where('role', ParticipantRole::Attendee->value);
            })
            ->exists();

        if ($existeTurno) {
            return;
        }

        Appointments::schedule('Consulta de demostración')
            ->startingAt($date->setTime(10, 0)->format('Y-m-d H:i'), timezone: $timezone)
            ->lasting(60)
            ->withParticipant($odontologo, ParticipantRole::Organiser)
            ->withParticipant($paciente, ParticipantRole::Attendee)
            ->withStatus(Status::Confirmed)
            ->preventConflicts()
            ->create();
    }
}
