@extends('layouts.app')

@section('title', 'Mis turnos')

@section('content')

<h1>Mis turnos</h1>

@forelse ($appointments as $appointment)
    <article>
        <h2>{{ $appointment->name }}</h2>
        <p>
            {{ $appointment->starts_at->format('d/m/Y H:i') }}
            @if ($appointment->ends_at) - {{ $appointment->ends_at->format('H:i') }}
            @endif
        </p>
    </article>
@empty
    <p>No hay turnos reservados.</p>
@endforelse

<nav>
    <a href="{{ route('reserva') }}">Reservar otro turno</a>
    <a href="{{ route('inicio') }}">Inicio</a>
    <a href="{{ route('perfil') }}">Mi perfil</a>
</nav>
@endsection