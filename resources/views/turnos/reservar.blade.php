@extends('layouts.app')

@section('title', 'Reservá tu turno')

@section('content')

<h1>Reservar un turno</h1>

<form action="{{ route('reserva.store') }}" method="POST">
    @csrf

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

    <label for="odontologo">Selecciona un odontólogo</label>
    <select name="odontologo_id" id="odontologo" required>
        <option value="">Selecciona un odontólogo</option>

        @foreach ($odontologos as $odontologo)
            <option value="{{ $odontologo->id }}" @selected(old('odontologo_id') == $odontologo->id)>
                {{ $odontologo->user->name }} {{ $odontologo->user->apellido }}
            </option>
        @endforeach
    </select>

    @error('odontologo_id')
        <p role="alert">{{ $message }}</p>
    @enderror

    <label for="fecha">Elegí la fecha</label>
    <input
        type="date"
        name="fecha"
        id="fecha"
        min="{{ now()->toDateString() }}"
        value="{{ old('fecha') }}"
        required
    >

    @error('fecha')
        <p role="alert">{{ $message }}</p>
    @enderror

    <label for="horario">Elegí un horario</label>
    <select name="horario" id="horario" required disabled>
        <option value="">Elegí odontólogo y fecha</option>
    </select>

    <p id="mensaje-horarios" role="status" aria-live="polite"></p>

    @error('horario')
        <p role="alert">{{ $message }}</p>
    @enderror

    <label for="observaciones">Observaciones</label>
    <textarea id="observaciones" name="observaciones" placeholder="(Opcional)">{{ old('observaciones') }}</textarea>

    @error('observaciones')
        <p role="alert">{{ $message }}</p>
    @enderror

    <button type="submit">Confirmar reserva</button>
</form>

<script>
    const selectOdontologo = document.getElementById('odontologo');
    const inputFecha = document.getElementById('fecha');
    const selectHorario = document.getElementById('horario');
    const mensajeHorarios = document.getElementById('mensaje-horarios');
    const urlHorarios = @json(route('reserva.horarios'));

    async function cargarHorarios() {
        selectHorario.disabled = true;
        mensajeHorarios.textContent = '';

        if (!selectOdontologo.value || !inputFecha.value) {
            selectHorario.replaceChildren(new Option('Elegí odontólogo y fecha', ''));
            return;
        }

        selectHorario.replaceChildren(new Option('Buscando horarios…', ''));

        try {
            const url = new URL(urlHorarios, window.location.origin);
            url.searchParams.set('odontologo_id', selectOdontologo.value);
            url.searchParams.set('fecha', inputFecha.value);

            const respuesta = await fetch(url, {
                headers: { Accept: 'application/json' },
            });

            if (!respuesta.ok) {
                throw new Error('No se pudieron cargar los horarios.');
            }

            const datos = await respuesta.json();
            selectHorario.replaceChildren(new Option('Seleccioná un horario', ''));

            for (const hora of datos.horarios) {
                selectHorario.add(new Option(hora, hora));
            }

            if (datos.horarios.length === 0) {
                mensajeHorarios.textContent = 'No hay horarios disponibles para esa fecha.';
                return;
            }

            selectHorario.disabled = false;
        } catch (error) {
            selectHorario.replaceChildren(new Option('Error al cargar horarios', ''));
            mensajeHorarios.textContent = 'No se pudieron cargar los horarios. Probá de nuevo.';
        }
    }

    selectOdontologo.addEventListener('change', cargarHorarios);
    inputFecha.addEventListener('change', cargarHorarios);

    if (selectOdontologo.value && inputFecha.value) {
        cargarHorarios();
    }
</script>

@endsection
