```blade
@extends('layouts.app')

@section('title', 'Turno confirmado')

@section('content')

    <div class="container py-4">

        <div class="border p-4 mb-4">

            <h1 class="mb-3">¡Tu turno está confirmado!</h1>

            <p class="mb-4">Estos son los datos de tu turno:</p>

            <dl class="row mb-0">

                <dt class="col-sm-3">Consulta</dt>
                <dd class="col-sm-9">{{ $appointment->name }}</dd>

                <dt class="col-sm-3">Fecha</dt>
                <dd class="col-sm-9">
                    {{ $appointment->starts_at->format('d/m/Y') }}
                </dd>

                <dt class="col-sm-3">Horario</dt>
                <dd class="col-sm-9">
                    {{ $appointment->starts_at->format('H:i') }}
                    @if ($appointment->ends_at)
                        - {{ $appointment->ends_at->format('H:i') }}
                    @endif
                </dd>

                @if ($appointment->description)
                    <dt class="col-sm-3">Observaciones</dt>
                    <dd class="col-sm-9">{{ $appointment->description }}</dd>
                @endif

            </dl>

        </div>

        @if ($consultorio)

            <section class="border p-4 mb-4">

                <h2 class="h4 mb-3">Datos del consultorio</h2>

                <dl class="row mb-0">

                    <dt class="col-sm-3">Dirección</dt>
                    <dd class="col-sm-9">{{ $consultorio->direccion }}</dd>

                    <dt class="col-sm-3">Teléfono</dt>
                    <dd class="col-sm-9">
                        <a href="tel:{{ $consultorio->telefono }}">
                            {{ $consultorio->telefono }}
                        </a>
                    </dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">
                        <a href="mailto:{{ $consultorio->email }}">
                            {{ $consultorio->email }}
                        </a>
                    </dd>

                </dl>

            </section>

        @else

            <div class="alert alert-info mb-4">
                Los datos del consultorio estarán disponibles próximamente.
            </div>

        @endif

        <nav class="d-flex gap-2">

            <a href="{{ url('/mis-turnos') }}" class="btn btn-primary">
                Mis turnos
            </a>

            <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                Inicio
            </a>

        </nav>

    </div>

@endsection
```