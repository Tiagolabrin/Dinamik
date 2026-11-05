@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
    <div class="container mt-5" style="max-width: 450px;">

        <h2 class="text-center mb-4">Iniciar sesión</h2>

        @if (session('success'))
            <div class="alert alert-success rounded-0">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger rounded-0">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger rounded-0">
                <p>Revisá estos datos:</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    class="form-control rounded-0"
                    id="email"
                    name="email"
                    required
                    autocomplete="email"
                    placeholder="correo@ejemplo.com"
                    value="{{ old('email') }}"
                >
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input
                    type="password"
                    class="form-control rounded-0"
                    name="password"
                    id="password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn btn-primary rounded-0 w-100">
                Ingresar
            </button>
        </form>

        <nav class="text-center mt-3">
            <a href="{{ route('registro') }}">Crear una cuenta</a>
            <span class="mx-2">|</span>
            <a href="{{ route('inicio') }}">Inicio</a>
        </nav>

    </div>
@endsection