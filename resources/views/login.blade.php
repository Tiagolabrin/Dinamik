@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
    <h1>Iniciar sesión</h1>

    @if (session('error'))
        <p role="alert">{{ session('error') }}</p>
    @endif

    <form action="{{ route('login.submit') }}" method="POST">
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
        <input
            type="email"
            id="email"
            name="email"
            required
            autocomplete="email"
            placeholder="correo@ejemplo.com"
            value="{{ old('email') }}"
        >

        <label for="password">Contraseña</label>
        <input
            type="password"
            name="password"
            id="password"
            required
            autocomplete="current-password"
        >

        <button type="submit">Ingresar</button>
    </form>

    <nav>
        <a href="{{ route('registro') }}">Registrar</a>
        <a href="{{ route('inicio') }}">Inicio</a>
    </nav>
@endsection
