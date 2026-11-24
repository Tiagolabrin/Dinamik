<?php

namespace Tests\Feature;

use App\Models\Odontologo;
use App\Models\Paciente;
use App\Models\User;
use App\Notifications\TurnoCanceladoNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RoundlyConsulting\Appointments\Enums\ParticipantRole;
use RoundlyConsulting\Appointments\Enums\Status;
use RoundlyConsulting\Appointments\Facades\Appointments;
use RoundlyConsulting\Appointments\Models\Appointment;
use Tests\TestCase;

class TurnoCancelacionNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_canceling_a_turn_saves_a_notification_for_both_participants(): void
    {
        config(['app.timezone' => 'America/Argentina/Buenos_Aires']);
        $this->travelTo('2026-11-24 08:00:00');

        $pacienteUser = User::query()->create([
            'name' => 'Ana',
            'apellido' => 'Paciente',
            'email' => 'ana@example.test',
            'password' => Hash::make('password-segura'),
            'tipo_usuario' => 'paciente',
        ]);
        Paciente::query()->create([
            'user_id' => $pacienteUser->id,
            'obra_social' => 'ISSN',
            'fecha_nacimiento' => '1990-01-01',
            'direccion' => 'Calle de prueba 123',
        ]);

        $odontologoUser = User::query()->create([
            'name' => 'Juan',
            'apellido' => 'Odontólogo',
            'email' => 'juan@example.test',
            'password' => Hash::make('password-segura'),
            'tipo_usuario' => 'odontologo',
        ]);
        $odontologo = Odontologo::query()->create([
            'user_id' => $odontologoUser->id,
            'matricula' => 'MAT-TEST-001',
        ]);

        $appointment = Appointments::schedule('Control dental')
            ->startingAt('2026-11-25 10:00', timezone: 'America/Argentina/Buenos_Aires')
            ->lasting(60)
            ->withParticipant($odontologo, ParticipantRole::Organiser)
            ->withParticipant($pacienteUser, ParticipantRole::Attendee)
            ->withStatus(Status::Confirmed)
            ->create();

        $this->actingAs($pacienteUser)
            ->post(route('turnos.cancelar', $appointment))
            ->assertRedirect(route('mis_turnos'))
            ->assertSessionHas('success', 'El turno se canceló correctamente.');

        $this->assertSame(Status::Cancelled, $appointment->fresh()->status);
        $this->assertDatabaseCount('notifications', 2);
        $this->post(route('turnos.cancelar', $appointment))
            ->assertRedirect(route('mis_turnos'))
            ->assertSessionHas('error', 'Este turno ya fue cancelado o no se puede cancelar.');
        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseHas('notifications', [
            'type' => TurnoCanceladoNotification::class,
            'notifiable_type' => $pacienteUser->getMorphClass(),
            'notifiable_id' => $pacienteUser->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'type' => TurnoCanceladoNotification::class,
            'notifiable_type' => $odontologoUser->getMorphClass(),
            'notifiable_id' => $odontologoUser->id,
        ]);

        $pacienteNotifications = $this->getJson(route('notificaciones.no-leidas'))
            ->assertOk()
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath(
                'notifications.0.message',
                'El turno «Control dental», programado para 25/11/2026 10:00, fue cancelado por Ana Paciente.',
            );
        $notificationId = $pacienteNotifications->json('notifications.0.id');

        $this->postJson(route('notificaciones.leidas'), ['ids' => [$notificationId]])
            ->assertNoContent();

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
            'notifiable_id' => $pacienteUser->id,
            'read_at' => now(),
        ]);
        $this->assertDatabaseHas('notifications', [
            'type' => TurnoCanceladoNotification::class,
            'notifiable_id' => $odontologoUser->id,
            'read_at' => null,
        ]);

        $this->actingAs($odontologoUser)
            ->getJson(route('notificaciones.no-leidas'))
            ->assertOk()
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath(
                'notifications.0.message',
                'El turno «Control dental», programado para 25/11/2026 10:00, fue cancelado por Ana Paciente.',
            );
    }

    public function test_notification_endpoints_require_authentication(): void
    {
        $this->getJson(route('notificaciones.no-leidas'))
            ->assertUnauthorized();

        $this->postJson(route('notificaciones.leidas'), ['ids' => ['a5b9193a-12f3-4f4a-9cbd-933988ac8fd8']])
            ->assertUnauthorized();
    }
}
