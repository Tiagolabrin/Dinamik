@extends('layouts.app')

@section('title', 'Registrar paciente')

@section('content')
    <div class="container mt-5" style="max-width: 750px;">

        <h2 class="text-center mb-4">Crear cuenta de paciente</h2>

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

            <form action="{{ route('registro.store') }}" method="POST"></form>
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input
                        type="text"
                        class="form-control rounded-0"
                        id="name"
                        name="name"
                        required
                        placeholder="Nombre"
                        value="{{ old('name') }}"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input
                        type="text"
                        class="form-control rounded-0"
                        id="apellido"
                        name="apellido"
                        required
                        placeholder="Apellido"
                        value="{{ old('apellido') }}"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        class="form-control rounded-0"
                        id="email"
                        name="email"
                        required
                        placeholder="correo@ejemplo.com"
                        value="{{ old('email') }}"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="obra_social" class="form-label">Obra social</label>
                    <input
                        type="text"
                        class="form-control rounded-0"
                        id="obra_social"
                        name="obra_social"
                        required
                        placeholder="ISSN"
                        value="{{ old('obra_social') }}"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input
                        type="password"
                        class="form-control rounded-0"
                        id="password"
                        name="password"
                        required
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                    <input
                        type="password"
                        class="form-control rounded-0"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                    <input
                        type="date"
                        class="form-control rounded-0"
                        id="fecha_nacimiento"
                        name="fecha_nacimiento"
                        required
                        value="{{ old('fecha_nacimiento') }}"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input
                        type="text"
                        class="form-control rounded-0"
                        id="direccion"
                        name="direccion"
                        required
                        value="{{ old('direccion') }}"
                    >
                </div>
            </div>

            <button type="submit" class="btn btn-primary rounded-0 w-100 mt-2">
                Registrar
            </button>
        </form>

        <nav class="text-center mt-3">
            <a href="{{ route('login') }}">Iniciar sesión</a>
            <span class="mx-2">|</span>
            <a href="{{ route('inicio') }}">Inicio</a>
        </nav>

    </div>
@endsection