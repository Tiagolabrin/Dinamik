@extends('layouts.app')

@section('title', 'Crear odontólogo')

@section('content')
    <main class="container py-4">
        <h2>Crear cuenta de odontólogo</h2>        
        @if (session('success'))
            <p class="alert alert-success" role="status">{{ session('success') }}</p>
        @endif

        <form action="{{ route('admin.odontologos.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input
                    type="text"
                    id="apellido"
                    name="apellido"
                    class="form-control"
                    value="{{ old('apellido') }}"
                    required
                >
                @error('apellido')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    required
                >
                @error('email')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label for="matricula" class="form-label">Matrícula</label>
                <input
                    type="text"
                    id="matricula"
                    name="matricula"
                    class="form-control"
                    value="{{ old('matricula') }}"
                    required
                >
                @error('matricula')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña temporal</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    required
                >
                @error('password')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary">Crear odontólogo</button>
        </form>
    </main>
@endsection