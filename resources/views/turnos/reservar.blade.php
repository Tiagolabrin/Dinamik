@extends('layouts.app')

@section('content')

<h1>Reservar un turno</h1>

<form action=" " method="POST">

    <label for="odontologo" >Selecciona un odontólogo</label>
    <select name="odontologo_id" id="odontologo" required>
        <option value="">Selecciona un odontologo</option>

        @foreach ($odontologos as $odontologo) 
            <option value="{{ $odontologo->id }}">
                {{ $odontologo->user->nombre }} {{ $odontologo->user->apellido }}
            </option>

        @endforeach
    </select>
    
    <label for="fecha">Elegí la fecha</label>
    <input type="date" name="fecha" id="fecha" required>

    <label for="horario">Elegí un horario</label>
    <select name="horario" id="horario" required>
        <option value="">Seleccioná un horario disponible</option>
    </select>

    <label for="observaciones">Observaciones</label>
    <textarea id="observaciones" name="observaciones" placeholder="(Opcional)"></textarea>

    <button type="submit">Confirmar reserva</button>
</form>

@endsection