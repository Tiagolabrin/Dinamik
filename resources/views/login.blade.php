@extends('layouts.app')

@section('content')

    <h1>Iniciar Sesión</h1>
    
    <form action="" method="POST">
        @csrf

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com">

        <label for="password">Contraseña</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Ingresar</button>
    </form>

    <nav>
    <a href="{{ route('registro') }}">Registrar</a>
    </nav>
    
@endsection