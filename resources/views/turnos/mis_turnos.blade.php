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

    @forelse ($appointments as $appointment)
        <article class="border p-3 mb-3">
            <h2 class="h5 mb-3">{{ $appointment->name }}</h2>
            <p class="mb-0">
                <strong>Fecha y horario:</strong>
                {{ $appointment->starts_at->setTimezone(config('app.timezone'))->format('d/m/Y H:i') }}
                @if ($appointment->ends_at)
                    - {{ $appointment->ends_at->setTimezone(config('app.timezone'))->format('H:i') }}
                @endif
            </p>
        </article>
    @empty
        <p class="border p-3">No tenés turnos reservados.</p>
    @endforelse

    <nav class="d-flex flex-wrap gap-2 mt-3">
        <a href="{{ route('reserva') }}" class="btn btn-primary">Reservar otro turno</a>
        <a href="{{ route('inicio') }}" class="btn btn-outline-secondary">Inicio</a>
        <a href="{{ route('perfil') }}" class="btn btn-outline-secondary">Mi perfil</a>
    </nav>
</div>
@endsection
