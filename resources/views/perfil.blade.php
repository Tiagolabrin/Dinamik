@extends('layouts.app')

@section('title','Mi perfil')

@section('content')

    
    @if (session('success'))
        <p class="alert alert-success" role="status">{{ session('success') }}</p>
    @endif
<head>
    <style>
        .perfil-container {
            width: min(100% - 2rem, 700px);
            margin: 2rem auto;
        }
        .mio{
            margin: 0 35rem 0 0;
        }
    </style>
</head>

</br>
    <div class="mio"><h1 class="h2 text-center fw-bold m5-4">Mi perfil</h1></div>
<div class="perfil-container"><div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-4">Datos del usuario</h5>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="bg-light rounded-3 p-3 h-100">
                    <dl class="mb-0">
                        <dt class="small text-secondary mb-1">Nombre</dt>
                        <dd class="fw-semibold mb-0">
                            {{ $user->name }} {{ $user->apellido }}
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="col-md-6">
                <div class="bg-light rounded-3 p-3 h-100">
                    <dl class="mb-0">
                        <dt class="small text-secondary mb-1">Email</dt>
                        <dd class="fw-semibold mb-0 text-break">{{ $user->email }}</dd>
                    </dl>
                </div>
            </div>

            <div class="col-md-6">
                <div class="bg-light rounded-3 p-3 h-100">
                    <dl class="mb-0">
                        <dt class="small text-secondary mb-1">Registrado como</dt>
                        <dd class="fw-semibold mb-0">{{ $user->tipo_usuario }}</dd>
                    </dl>
                </div>
            </div>

            @if ($user->paciente)
                <div class="col-md-6">
                    <div class="bg-light rounded-3 p-3 h-100">
                        <dl class="mb-0">
                            <dt class="small text-secondary mb-1">Obra social</dt>
                            <dd class="fw-semibold mb-0">{{ $user->paciente->obra_social }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="bg-light rounded-3 p-3 h-100">
                        <dl class="mb-0">
                            <dt class="small text-secondary mb-1">Fecha de nacimiento</dt>
                            <dd class="fw-semibold mb-0">{{ $user->paciente->fecha_nacimiento }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="bg-light rounded-3 p-3 h-100">
                        <dl class="mb-0">
                            <dt class="small text-secondary mb-1">Dirección</dt>
                            <dd class="fw-semibold mb-0">{{ $user->paciente->direccion }}</dd>
                        </dl>
                    </div>
                </div>
            @elseif ($user->odontologo)
                <div class="col-md-6">
                    <div class="bg-light rounded-3 p-3 h-100">
                        <dl class="mb-0">
                            <dt class="small text-secondary mb-1">Matrícula</dt>
                            <dd class="fw-semibold mb-0">{{ $user->odontologo->matricula }}</dd>
                        </dl>
                    </div>
                </div>
            @endif
        </div>

<div class="text-center">
    <nav>
        <a href="{{ route('mis_turnos') }}" class="btn btn-success mt-3">Mis Turnos</a>
        <a href="{{ route('perfil_editar') }}" class="btn btn-primary mt-3">Editar mi perfil</a>
        <a href="{{ route('inicio') }}" class="btn btn-outline-primary mt-3">Inicio</a>
</div>
        @if ($user->odontologo)
            <h5 class="card-title mt-5 mb-3">Mi disponibilidad</h5>

            @php
                $disponibilidadesPorDia = $user->odontologo->disponibilidades->groupBy('dia_semana');
            @endphp

            @forelse ($disponibilidadesPorDia as $dia => $horarios)
                <div class="bg-light rounded-3 p-3 mb-3">
                    <h6 class="fw-semibold mb-2 text-capitalize">{{ $dia }}</h6>

                    @foreach ($horarios as $horario)
                        <p class="mb-1">
                            {{ substr($horario->hora_inicio, 0, 5) }} - {{ substr($horario->hora_fin, 0, 5) }}
                        </p>
                    @endforeach
                </div>
            @empty
                <p class="text-secondary">Todavía no cargaste tus horarios.</p>
            @endforelse

            <h5 class="card-title mt-5 mb-3">Mis vacaciones</h5>

            @forelse ($user->odontologo->vacaciones as $vacacion)
                <div class="bg-light rounded-3 p-3 mb-3">
                    <p class="fw-semibold mb-0">
                        {{ $vacacion->fecha_desde }} - {{ $vacacion->fecha_hasta }}
                    </p>
                </div>
            @empty
                <p class="text-secondary">No tenés vacaciones cargadas.</p>
            @endforelse
        @endif
    </div>
</div>
</div>
@endsection
