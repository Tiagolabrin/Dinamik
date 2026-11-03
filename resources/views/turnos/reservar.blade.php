@extends('layouts.app')

@section('title', 'Reservá tu turno')


@section('content')

<h1>Reservar un turno</h1>

<form action=" " method="POST">
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

    <label for="odontologo" >Selecciona un odontólogo</label>
    <select name="odontologo_id" id="odontologo" required>
        <option value="">Selecciona un odontologo</option>

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
    <input type="date" name="fecha" id="fecha" value="{{ old('fecha') }}" required>
    
    @error('fecha')
        <p role="alert">{{ $message }}</p>
    @enderror

    <label for="horario">Elegí un horario</label>
    <select name="horario" id="horario" required>
        <option value="">Seleccioná un horario disponible</option>
    </select>

    @error("horario")
        <p role="alert">{{ $message }}</p>
    @enderror

    <label for="observaciones">Observaciones</label>
    <textarea id="observaciones" name="observaciones" placeholder="(Opcional)" >{{ old('observaciones') }}</textarea>
    
    @error('observaciones')
        <p role="alert">{{ $message }}</p>
    @enderror 

    <button type="submit">Confirmar reserva</button>
</form>

@endsection