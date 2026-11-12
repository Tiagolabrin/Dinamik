@extends('layouts.app')

@section('title','Editar')

@section('content')


    @if ($errors->any())
        <div role="alert">
            <p>Revisá estos datos:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
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
        <div class="mio"><h1 class="h2 text-center fw-bold m5-4">Editar mi perfil</h1></div>
<div class="perfil-container"><div class="card shadow-sm">
<form action="{{ route('perfil.update') }}" method="POST">
    @csrf
<div class="container mt-4">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}">
        </div>

        <div class="col-md-6 mb-3">
            <label for="apellido" class="form-label">Apellido</label>
            <input type="text" class="form-control" id="apellido" name="apellido" value="{{ old('apellido', $user->apellido) }}">
        </div>

        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}">
        </div>
    </div>

    @if ($user->paciente)
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="obra_social" class="form-label">Obra social</label>
                <input type="text" class="form-control" id="obra_social" name="obra_social" value="{{ old('obra_social', $user->paciente->obra_social) }}">
            </div>

            <div class="col-md-6 mb-3">
                <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $user->paciente->fecha_nacimiento) }}">
            </div>

            <div class="col-12 mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" value="{{ old('direccion', $user->paciente->direccion) }}">
            </div>
        </div>
    @elseif ($user->odontologo)

        <label for="matricula">Matrícula</label>
        <input type="text" id="matricula" name="matricula" value="{{ old('matricula', $user->odontologo->matricula) }}">

        <section id="disponibilidad">
            <h2>Horarios habituales</h2>

            @forelse ($user->odontologo->disponibilidades as $indice => $disponibilidad)
                <div class="fila-disponibilidad">
                    <label for="dia-{{ $indice }}">Día</label>
                    <select id="dia-{{ $indice }}" name="disponibilidades[{{ $indice }}][dia_semana]" required>
                        <option value="">Elegí un día</option>
                        <option value="lunes" @selected(old("disponibilidades.$indice.dia_semana", $disponibilidad->dia_semana) === 'lunes')>Lunes</option>
                        <option value="martes" @selected(old("disponibilidades.$indice.dia_semana", $disponibilidad->dia_semana) === 'martes')>Martes</option>
                        <option value="miercoles" @selected(old("disponibilidades.$indice.dia_semana", $disponibilidad->dia_semana) === 'miercoles')>Miércoles</option>
                        <option value="jueves" @selected(old("disponibilidades.$indice.dia_semana", $disponibilidad->dia_semana) === 'jueves')>Jueves</option>
                        <option value="viernes" @selected(old("disponibilidades.$indice.dia_semana", $disponibilidad->dia_semana) === 'viernes')>Viernes</option>
                    </select>

                    <label for="inicio-{{ $indice }}">Desde</label>
                    <input type="time" id="inicio-{{ $indice }}" name="disponibilidades[{{ $indice }}][hora_inicio]" value="{{ old("disponibilidades.$indice.hora_inicio", substr($disponibilidad->hora_inicio, 0, 5)) }}" required>

                    <label for="fin-{{ $indice }}">Hasta</label>
                    <input type="time" id="fin-{{ $indice }}" name="disponibilidades[{{ $indice }}][hora_fin]" value="{{ old("disponibilidades.$indice.hora_fin", substr($disponibilidad->hora_fin, 0, 5)) }}" required>
                </div>
            @empty
                <div class="fila-disponibilidad">
                    <label for="dia-0">Día</label>
                    <select id="dia-0" name="disponibilidades[0][dia_semana]" required>
                        <option value="">Elegí un día</option>
                        <option value="lunes">Lunes</option>
                        <option value="martes">Martes</option>
                        <option value="miercoles">Miércoles</option>
                        <option value="jueves">Jueves</option>
                        <option value="viernes">Viernes</option>
                    </select>

                    <label for="inicio-0">Desde</label>
                    <input type="time" id="inicio-0" name="disponibilidades[0][hora_inicio]" required>

                    <label for="fin-0">Hasta</label>
                    <input type="time" id="fin-0" name="disponibilidades[0][hora_fin]" required>
                </div>
            @endforelse

            <button type="button" id="agregar-disponibilidad">Agregar otro horario</button>
        </section>

        <section id="vacaciones">
            <h2>Vacaciones y días no disponibles</h2>

            @forelse ($user->odontologo->vacaciones as $indice => $vacacion)
                <div class="fila-vacaciones">
                    <label for="fecha-desde-{{ $indice }}">Desde</label>
                    <input type="date" id="fecha-desde-{{ $indice }}" name="vacaciones[{{ $indice }}][fecha_desde]" value="{{ old("vacaciones.$indice.fecha_desde", $vacacion->fecha_desde) }}">

                    <label for="fecha-hasta-{{ $indice }}">Hasta</label>
                    <input type="date" id="fecha-hasta-{{ $indice }}" name="vacaciones[{{ $indice }}][fecha_hasta]" value="{{ old("vacaciones.$indice.fecha_hasta", $vacacion->fecha_hasta) }}">
                </div>
            @empty
                <div class="fila-vacaciones">
                    <label for="fecha-desde-0">Desde</label>
                    <input type="date" id="fecha-desde-0" name="vacaciones[0][fecha_desde]">

                    <label for="fecha-hasta-0">Hasta</label>
                    <input type="date" id="fecha-hasta-0" name="vacaciones[0][fecha_hasta]">
                </div>
            @endforelse
                <button type="button" id="agregar-vacaciones">Agregar otro período</button>
            </section>
        @endif
<div class="w-100 text-center mb-4">
    <button type="submit" class="btn btn-primary">Guardar cambios</button>
</div>

    </form>

</div>
</div>
<div class="text-center">
    <nav>
        <a href="{{ route('mis_turnos') }}" class="btn btn-success mt-3">Mis Turnos</a>
        <a href="{{ route('inicio') }}" class="btn btn-outline-primary mt-3">Inicio</a>
</div>
    <script>
        const seccionDisponibilidad = document.querySelector('#disponibilidad');
        const botonAgregar = document.querySelector('#agregar-disponibilidad');
        let siguienteIndice = seccionDisponibilidad ? 
                                seccionDisponibilidad.querySelectorAll('.fila-disponibilidad').length
                                : 0 ;

        if (seccionDisponibilidad && botonAgregar){
            botonAgregar.addEventListener('click', () =>{
                const primeraFila = seccionDisponibilidad.querySelector('.fila-disponibilidad');
                const nuevaFila = primeraFila.cloneNode(true);

                nuevaFila.querySelectorAll('input, select').forEach( (campo) => {
                    campo.value = "";
                    campo.name = campo.name.replace(/\[\d+\]/,`[${siguienteIndice}]`);
                    campo.id = campo.id.replace(/-\d+$/, `-${siguienteIndice}`);
                });

                nuevaFila.querySelectorAll('label').forEach( (etiqueta) => {
                    etiqueta.htmlFor = etiqueta.htmlFor.replace(/-\d+$/, `-${siguienteIndice}`);
                });

                const botonQuitar = document.createElement('button');
                botonQuitar.type = 'button';
                botonQuitar.textContent = 'Quitar horario';
                botonQuitar.addEventListener('click', () => nuevaFila.remove());

                nuevaFila.append(botonQuitar);
                seccionDisponibilidad.insertBefore(nuevaFila, botonAgregar);
                siguienteIndice++;
            });
        }

        const seccionVacaciones = document.querySelector('#vacaciones');
        const botonAgregarVacaciones = document.querySelector('#agregar-vacaciones');
        let siguientePeriodo = seccionVacaciones ? 
                                seccionVacaciones.querySelectorAll('.fila-vacaciones').length
                                : 0 ;

        if (seccionVacaciones && botonAgregarVacaciones) {
            botonAgregarVacaciones.addEventListener('click',() => {
                const primeraFila = seccionVacaciones.querySelector('.fila-vacaciones');
                const nuevaFila = primeraFila.cloneNode(true);

                nuevaFila.querySelectorAll('input').forEach( (campo) => {
                    campo.value = "";
                    campo.name = campo.name.replace(/\[\d+\]/, `[${siguientePeriodo}]`);
                    campo.id = campo.id.replace(/-\d+$/,`-${siguientePeriodo}`);
                });

                nuevaFila.querySelectorAll('label').forEach( (etiqueta) => {
                    etiqueta.htmlFor = etiqueta.htmlFor.replace(/-\d+$/, `-${siguientePeriodo}`);
                });

                const botonQuitar = document.createElement('button')
                botonQuitar.type = "button";
                botonQuitar.textContent = "Quitar período";
                botonQuitar.addEventListener('click', () => nuevaFila.remove());

                nuevaFila.append(botonQuitar);
                seccionVacaciones.insertBefore(nuevaFila, botonAgregarVacaciones);
                siguientePeriodo++;
            });
        }
    </script>
@endsection
