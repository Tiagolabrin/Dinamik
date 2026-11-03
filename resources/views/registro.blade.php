@extends('layouts.app')

@section('title','Registrar paciente')

@section('content')
    <h2>Crear cuenta de paciente</h2>

    <form action="" method="POST">
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

        <label for="name">Nombre</label>
        <input type="text" id="name" name="name" required placeholder="Nombre" value="{{ old('name') }}">
        
        @error('name')
            <p role="alert">{{ $message }}</p>
        @enderror

        <label for="apellido">Apellido</label>
        <input type="text" id="apellido" name="apellido" required placeholder="Apellido" value="{{ old('apellido') }}">
        
        @error('apellido')
            <p role="alert">{{ $message }}</p>
        @enderror

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com" value="{{ old('email') }}">
        
        @error('email')
            <p role="alert">{{ $message }}</p>
        @enderror

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required >
        
        @error('password')
            <p role="alert">{{ $message }}</p>
        @enderror

        <label for="password_confirmation">Confirmar contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
        
        @error('password_confirmation')
            <p role="alert">{{ $message }}</p>
        @enderror

        <label for="obra_social">Obra social</label>
        <input type="text" id="obra_social" name="obra_social" required placeholder="ISSN" value="{{ old('obra_social') }}">
        
        @error('obra_social')
            <p role="alert">{{ $message }}</p>
        @enderror

        <label for="fecha_nacimiento">Fecha de nacimiento</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required value="{{ old('fecha_nacimiento') }}">
            
        @error('fecha_nacimiento')
            <p role="alert">{{ $message }}</p>
        @enderror

        <label for="direccion">Dirección</label>
        <input type="text" id="direccion" name="direccion" required value="{{ old('direccion') }}">
        
        @error('direccion')
            <p role="alert">{{ $message }}</p>
        @enderror

        <button type="submit">Registrar</button>
    </form>

@endsection