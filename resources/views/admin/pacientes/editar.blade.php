@extends('layouts.app')

@section('title', 'Editar paciente')

@section('content')
<main class="container py-4">

    <h2 class="mb-4">Editar paciente</h2>

    <form action="{{ route('admin.pacientes.update', $paciente) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control"
                    value="{{ old('name', $paciente->user->name) }}">
            @error('name')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Apellido</label>
            <input type="text" name="apellido" class="form-control"
                    value="{{ old('apellido', $paciente->user->apellido) }}">
            @error('apellido')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Obra social</label>
            <input type="text" name="obra_social" class="form-control"
                    value="{{ old('obra_social', $paciente->obra_social) }}">
            @error('obra_social')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" class="form-control"
                    value="{{ old('fecha_nacimiento', $paciente->fecha_nacimiento) }}">
            @error('fecha_nacimiento')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" name="direccion" class="form-control"
                    value="{{ old('direccion', $paciente->direccion) }}">
            @error('direccion')
                <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Guardar cambios</button>

        <a href="{{ route('admin.pacientes.index') }}"
          class="btn btn-outline-secondary">
            Volver
        </a>

    </form>

</main>
@endsection