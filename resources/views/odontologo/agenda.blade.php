@extends('layouts.app')

@section('title', 'Agenda')

@section('content')

    <div class="container py-4">

        <h1 class="mb-4">Mi agenda</h1>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        @forelse ($appointments as $appointment)

            @php
                $participante = $appointment->participants->first(
                    fn ($participante) =>
                        $participante->role === \RoundlyConsulting\Appointments\Enums\ParticipantRole::Attendee
                );

                $paciente = $participante?->participant;
                $puedeCancelar = $appointment->starts_at->isFuture()
                    && $appointment->status->canTransitionTo(\RoundlyConsulting\Appointments\Enums\Status::Cancelled);
            @endphp

            <article class="border p-3 mb-3">

                <h2 class="h5 mb-3">
                    Consulta con
                    {{ $paciente?->name ?? 'Paciente no disponible' }}
                    {{ $paciente?->apellido ?? '' }}
                </h2>

                <p class="mb-2">
                    <strong>Fecha y horario:</strong>
                    {{ $appointment->starts_at->setTimezone(config('app.timezone'))->format('d/m/Y H:i') }}

                    @if ($appointment->ends_at)
                        - {{ $appointment->ends_at->setTimezone(config('app.timezone'))->format('H:i') }}
                    @endif
                </p>

                <p class="mb-2">
                    <strong>Estado:</strong> {{ $appointment->status->label() }}
                </p>

                @if ($appointment->description)
                    <p class="mb-0">
                        <strong>Observaciones:</strong>
                        {{ $appointment->description }}
                    </p>
                @endif

                @if ($puedeCancelar)
                    <form action="{{ route('turnos.cancelar', $appointment) }}" method="POST" class="mt-3"
                        onsubmit="return confirm('¿Estás seguro de que querés cancelar este turno?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">Cancelar turno</button>
                    </form>
                @endif

            </article>

        @empty

            <p class="border p-3">
                No hay turnos para mostrar.
            </p>

        @endforelse

        <a href="{{ route('inicio') }}" class="btn btn-outline-secondary mt-3">Volver al inicio</a>

    </div>

@endsection
