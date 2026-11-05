
@extends('layouts.app')

@section('title', 'Agenda')

@section('content')

    <h1>Mi agenda</h1>

    @forelse ($appointments as $appointment)

        @php
            $participante = $appointment->participants->first(
                fn ($participante) =>
                    $participante->role === \RoundlyConsulting\Appointments\Enums\ParticipantRole::Attendee
            );

            $paciente = $participante?->participant;
        @endphp

        <article>
            <h2>
                Consulta con
                {{ $paciente?->name ?? 'Paciente no disponible' }}
                {{ $paciente?->apellido ?? '' }}
            </h2>

            <p>
                {{ $appointment->starts_at->format('d/m/Y H:i') }}

                @if ($appointment->ends_at)
                    - {{ $appointment->ends_at->format('H:i') }}
                @endif
            </p>

            @if ($appointment->description)
                <p>
                    <strong>Observaciones:</strong>
                    {{ $appointment->description }}
                </p>
            @endif
        </article>

    @empty
        <p>No hay turnos para mostrar.</p>
    @endforelse

@endsection
