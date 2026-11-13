
@extends('layouts.app')

@section('title', 'Editar odontólogo')

@section('content')
<main class="container py-4">

    <h2 class="mb-4">Editar odontólogo</h2>

    <form action="{{ route('admin.odontologos.update', $odontologo) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control"
                   value="{{ old('name', $odontologo->user->name) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Apellido</label>
            <input type="text" name="apellido" class="form-control"
                    value="{{ old('apellido', $odontologo->user->apellido) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                    value="{{ old('email', $odontologo->user->email) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Matrícula</label>
            <input type="text" name="matricula" class="form-control"
                    value="{{ old('matricula', $odontologo->matricula) }}" required>
        </div>

        <button type="submit" class="btn btn-primary">
            Guardar cambios
        </button>

        <a href="{{ route('admin.odontologos.index') }}" class="btn btn-outline-secondary">
            Volver
        </a>
    </form>

</main>
@endsection
