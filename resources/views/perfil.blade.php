@extends('layouts.app')

@section('title','Mi perfil')

@section('content')
    <h1>Mi perfil</h1>

    <dl>
        <div>
            <dt>Nombre</dt>
            <dd>{{ $user->name }} {{ $user->apellido }}</dd>
        </div>

        <div>
            <dt>Email</dt>
            <dd>{{ $user->email }}</dd>
        </div>

        <div>
            <dt>Registrado como</dt>
            <dd>{{ $user->tipo_usuario }}</dd>
        </div>

        @if ($user->paciente)
            <div>
                <dt>Obra social</dt>
                <dd>{{ $user->paciente->obra_social }}</dd>
            </div>
            
            <div>
                <dt>Fecha de nacimiento</dt>
                <dd>{{ $user->paciente->fecha_nacimiento }}</dd>
            </div>

            <div>
                <dt>Dirección</dt>
                <dd>{{ $user->paciente->direccion }}</dd>
            </div>
        @elseif ($user->odontologo)
            <div>
                <dt>Matrícula</dt>
                <dd>{{ $user->odontologo->matricula }}</dd>
            </div>
        @endif
    </dl>

    @if ($user->odontologo)
        <section>
            <h2>Mi disponibilidad</h2>

            @php
                $disponibilidadesPorDia = $user->odontologo->disponibilidades->groupBy('dia_semana');
            @endphp

            @forelse ($disponibilidadesPorDia as $dia =>$horarios)
                <article>
                    <h3>{{ $dia }}</h3>

                    @foreach ($horarios as $horario)
                        <p>
                            {{ $horario->hora_inicio }} - {{ $horario->hora_fin }}
                        </p>
                    @endforeach
                </article>
            @empty
                <p>Todavía no cargaste tus horarios.</p>
            @endforelse
        </section>

        <section>
            <h2>Mis vacaciones</h2>

            @forelse ($user->odontologo->vacaciones as $vacacion)
                <article>
                    <p>
                        {{ $vacacion->fecha_desde }} - {{ $vacacion->fecha_hasta }}
                    </p>
                </article>
            @empty
                <p>No tenes vacaciones cargadas.</p>
            @endforelse
        </section>
    @endif

    <nav>
        <a href="{{ route('perfil_editar') }}">Editar mi perfil</a>
        <a href="{{ route('inicio') }}">Inicio</a>
        <a href="{{ route('mis_turnos') }}">Mis Turnos</a>
    </nav>
@endsection