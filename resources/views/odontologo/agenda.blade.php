```blade
@extends('layouts.app')

@section('title', 'Agenda')

@section('content')

    <div class="container py-4">

        <h1 class="mb-4">Mi agenda</h1>

        @forelse ($appointments as $appointment)

            @php
                $participante = $appointment->participants->first(
                    fn ($participante) =>
                        $participante->role === \RoundlyConsulting\Appointments\Enums\ParticipantRole::Attendee
                );

                $paciente = $participante?->participant;
            @endphp

            <article class="border p-3 mb-3">

                <h2 class="h5 mb-3">
                    Consulta con
                    {{ $paciente?->name ?? 'Paciente no disponible' }}
                    {{ $paciente?->apellido ?? '' }}
                </h2>

                <p class="mb-2">
                    <strong>Fecha y horario:</strong>
                    {{ $appointment->starts_at->format('d/m/Y H:i') }}

                    @if ($appointment->ends_at)
                        - {{ $appointment->ends_at->format('H:i') }}
                    @endif
                </p>

                @if ($appointment->description)
                    <p class="mb-0">
                        <strong>Observaciones:</strong>
                        {{ $appointment->description }}
                    </p>
                @endif

            </article>

        @empty

            <p class="border p-3">
                No hay turnos para mostrar.
            </p>

        @endforelse

    </div>

@endsection
```