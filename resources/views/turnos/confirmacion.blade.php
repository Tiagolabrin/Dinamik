@extends('layouts.app')

@section('title','Turno confirmado')

@section('content')
    <h1>¡Tu turno está confirmado!</h1>

    <dl>
        <div>
            <dt>Consulta</dt>
            <dd>{{ $appointment->name }}</dd>
        </div>

        <div>
            <dt>Fecha</dt>
            <dd>
                {{ $appointment->starts_at->format('d/m/Y') }}
            </dd>
        </div>

        <div>
            <dt>Horario</dt>
            <dd>
                {{ $appointment->starts_at->format('H:i') }}
                @if ($appointment->ends_at) - {{ $appointment->ends_at->format('H:i') }}
                @endif
            </dd>
        </div>

        @if ($appointment->observaciones)
            <div>
                <dt><strong>Observaciones:</strong></dt>
                <dd>{{ $appointment->observaciones }}</dd>
            </div>
        @endif
    </dl>

    @if ($consultorio)
        <section>
            <h2>Datos del consultorio</h2>
            <dl>
                <dt>Dirección</dt>
                <dd>{{ $consultorio->direccion }}</dd>

                <dt>Teléfono</dt>
                <dd>
                    <a href="tel:{{ $consultorio->telefono }}">
                        {{ $consultorio->telefono }}
                    </a>
                </dd>

                <dt>Email</dt>
                <dd>
                    <a href="mailto:{{ $consultorio->email }}">
                        {{ $consultorio->email }}
                    </a>
                </dd>
            </dl>
        </section>
    @endif
    <nav>
        <a href="{{ url('/mis-turnos') }}">Mis turnos</a>
        <a href="{{ url('/') }}">Inicio</a>
    </nav>
@endsection