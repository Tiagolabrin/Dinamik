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

        <form action="{{ route('registro.store') }}" method="POST">
        @csrf
        <div class="row g-2">
            <div class="col-md-6">
                <label for="name" class="form-label mb-1">Nombre</label>
                <input type="text" class="form-control form-control-sm rounded-0" id="name" name="name"
                       required placeholder="Nombre" value="{{ old('name') }}">
            </div>

            <div class="col-md-6">
                <label for="apellido" class="form-label mb-1">Apellido</label>
                <input type="text" class="form-control form-control-sm rounded-0" id="apellido" name="apellido"
                       required placeholder="Apellido" value="{{ old('apellido') }}">
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label mb-1">Email</label>
                <input type="email" class="form-control form-control-sm rounded-0" id="email" name="email"
                       required placeholder="correo@ejemplo.com" value="{{ old('email') }}">
            </div>

            <div class="col-md-6">
                <label for="obra_social" class="form-label mb-1">Obra social</label>
                <input type="text" class="form-control form-control-sm rounded-0" id="obra_social" name="obra_social"
                       required placeholder="ISSN" value="{{ old('obra_social') }}">
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label mb-1">Contraseña</label>
                <input type="password" class="form-control form-control-sm rounded-0" id="password" name="password" required>
            </div>

            <div class="col-md-6">
                <label for="password_confirmation" class="form-label mb-1">Confirmar contraseña</label>
                <input type="password" class="form-control form-control-sm rounded-0" id="password_confirmation"
                       name="password_confirmation" required>
            </div>

            <div class="col-md-6">
                <label for="fecha_nacimiento" class="form-label mb-1">Fecha de nacimiento</label>
                <input type="date" class="form-control form-control-sm rounded-0" id="fecha_nacimiento"
                       name="fecha_nacimiento" required value="{{ old('fecha_nacimiento') }}">
            </div>

            <div class="col-md-6">
                <label for="direccion" class="form-label mb-1">Dirección</label>
                <input type="text" class="form-control form-control-sm rounded-0" id="direccion" name="direccion"
                       required value="{{ old('direccion') }}">
            </div>

            <div class="col-12">
                <label for="foto" class="form-label mb-1">Foto de perfil (opcional)</label>
                <input type="file" class="form-control form-control-sm rounded-0" id="foto" name="foto"
                       accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPG, PNG o WEBP. Máximo 2 MB.</div>
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
