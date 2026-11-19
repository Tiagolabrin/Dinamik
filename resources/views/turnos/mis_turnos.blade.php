@extends('layouts.app')

@section('title', 'Mis turnos')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Mis turnos</h1>

    @if (session('success'))
        <div class="alert alert-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @forelse ($appointments as $appointment)
        @php
            $estado = match ($appointment->status) {
                \RoundlyConsulting\Appointments\Enums\Status::Pending => 'Pendiente',
                \RoundlyConsulting\Appointments\Enums\Status::Confirmed => 'Confirmado',
                \RoundlyConsulting\Appointments\Enums\Status::Cancelled => 'Cancelado',
                \RoundlyConsulting\Appointments\Enums\Status::Completed => 'Completado',
                \RoundlyConsulting\Appointments\Enums\Status::Declined => 'Rechazado',
                \RoundlyConsulting\Appointments\Enums\Status::NoShow => 'Ausente',
            };
            $puedeCancelar = $appointment->starts_at->isFuture()
                && $appointment->status->canTransitionTo(\RoundlyConsulting\Appointments\Enums\Status::Cancelled);
        @endphp

        <article class="border p-3 mb-3">
            <h2 class="h5 mb-3">{{ $appointment->name }}</h2>
            <p class="mb-2">
                <strong>Fecha y horario:</strong>
                {{ $appointment->starts_at->setTimezone(config('app.timezone'))->format('d/m/Y H:i') }}
                @if ($appointment->ends_at)
                    - {{ $appointment->ends_at->setTimezone(config('app.timezone'))->format('H:i') }}
                @endif
            </p>
            <p class="mb-0"><strong>Estado:</strong> {{ $estado }}</p>

            @if ($puedeCancelar)
                <form action="{{ route('turnos.cancelar', $appointment) }}" method="POST" class="mt-3"
                    onsubmit="return confirm('¿Estás seguro de que querés cancelar este turno?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Cancelar turno</button>
                </form>
            @endif
        </article>
    @empty
        <p class="border p-3">No tenés turnos para mostrar.</p>
    @endforelse

    <nav class="d-flex flex-wrap gap-2 mt-3">
        @if (auth()->user()->tipo_usuario === 'paciente')
            <a href="{{ route('reserva') }}" class="btn btn-primary">Reservar otro turno</a>
        @endif
        <a href="{{ route('inicio') }}" class="btn btn-outline-secondary">Inicio</a>
        <a href="{{ route('perfil') }}" class="btn btn-outline-secondary">Mi perfil</a>
    </nav>
</div>
@endsection
