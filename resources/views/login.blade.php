@extends('layouts.app')

@section('title','LogIN')

@section('content')

    <h1>Iniciar Sesión</h1>
    
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

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com" value="{{ old('email') }}">

        @error('email')
            <p role="alert">{{ $message }}</p>
        @enderror

        <label for="password">Contraseña</label>
        <input type="password" name="password" id="password" required>

        @error('password')
            <p role="alert">{{ $message }}</p>
        @enderror

        <button type="submit">Ingresar</button>
    </form>

    <nav>
    <a href="{{ route('registro') }}">Registrar</a>
    <a href="{{ route('inicio') }}">Inicio</a>
    </nav>
    
@endsection