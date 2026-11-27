<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RoundlyConsulting\Appointments\Enums\ParticipantRole;
use RoundlyConsulting\Appointments\Models\Appointment;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_loads_demo_data_without_duplicates(): void
    {
        config([
            'app.admin_initial.name' => 'Administradora de prueba',
            'app.admin_initial.email' => 'admin@example.test',
            'app.admin_initial.password' => 'clave-admin-segura',
            'app.demo_accounts.paciente_password' => 'clave-paciente-segura',
            'app.demo_accounts.odontologo_password' => 'clave-odontologa-segura',
            'appointments.timezone' => 'America/Argentina/Buenos_Aires',
        ]);
        $this->travelTo('2026-11-24 08:00:00');

        $this->seed();
        $this->seed();

        $paciente = User::query()->where('email', 'paciente.demo@example.test')->firstOrFail();
        $odontologo = User::query()->where('email', 'odontologa.demo@example.test')->firstOrFail();

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('pacientes', 1);
        $this->assertDatabaseCount('odontologos', 1);
        $this->assertDatabaseCount('disponibilidades', 5);
        $this->assertDatabaseCount('vacaciones', 1);
        $this->assertDatabaseCount('consultorios', 1);
        $this->assertDatabaseCount('appointments', 1);
        $this->assertDatabaseCount('appointment_participants', 2);
        $this->assertDatabaseHas('pacientes', ['user_id' => $paciente->id, 'obra_social' => 'ISSN']);
        $this->assertDatabaseHas('odontologos', ['user_id' => $odontologo->id, 'matricula' => 'MAT-DEMO-001']);
        $appointment = Appointment::query()->firstOrFail();
        $this->assertDatabaseHas('appointment_participants', [
            'appointment_id' => $appointment->id,
            'participant_type' => $paciente->getMorphClass(),
            'participant_id' => $paciente->id,
            'role' => ParticipantRole::Attendee->value,
        ]);
        $this->assertDatabaseHas('appointment_participants', [
            'appointment_id' => $appointment->id,
            'participant_type' => $odontologo->odontologo->getMorphClass(),
            'participant_id' => $odontologo->odontologo->id,
            'role' => ParticipantRole::Organiser->value,
        ]);
        $this->assertSame('paciente', $paciente->tipo_usuario);
        $this->assertSame('odontologo', $odontologo->tipo_usuario);
        $this->assertTrue(Hash::check('clave-paciente-segura', $paciente->password));
        $this->assertTrue(Hash::check('clave-odontologa-segura', $odontologo->password));
    }

    public function test_database_seeder_requires_demo_passwords(): void
    {
        config([
            'app.admin_initial.name' => 'Administradora de prueba',
            'app.admin_initial.email' => 'admin@example.test',
            'app.admin_initial.password' => 'clave-admin-segura',
            'app.demo_accounts.paciente_password' => null,
            'app.demo_accounts.odontologo_password' => null,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DEMO_PACIENTE_PASSWORD y DEMO_ODONTOLOGO_PASSWORD');

        $this->seed();
    }
}
