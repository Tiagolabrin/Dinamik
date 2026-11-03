@extends('layouts.app')

@section('title', 'Agenda')

@section('content')

    <h1>Mi agenda</h1>

    @forelse ($appointments as $appointment)
        <article>
            <h2>{{ $appointment->name }}</h2>
            <p>
                {{ $appointment->starts_at->format('d/m/y H:i') }}
                @if ($appointment->ends_at) - {{ $appointment->ends_at->format('H:i') }}
                @endif
            </p>
        </article>
    @empty 
        <p>No hay turnos para mostrar.</p>
    @endforelse
@endsection