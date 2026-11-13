@extends('layouts.app')

@section('title', 'Administrar consultorio')

@section('content')
<main class="container py-4">

    <h2 class="mb-4">Administrar consultorio</h2>

    @if (session('success'))
        <p class="alert alert-success">{{ session('success') }}</p>
    @endif

    <form action="{{ route('admin.consultorio.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" name="direccion" class="form-control"
                    value="{{ old('direccion', $consultorio?->direccion) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control"
                    value="{{ old('telefono', $consultorio?->telefono) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                    value="{{ old('email', $consultorio?->email) }}" required>
        </div>

        <button type="submit" class="btn btn-primary">
            Guardar cambios
        </button>

        <a href="{{ route('inicio') }}" class="btn btn-outline-secondary">
            Volver
        </a>
    </form>

</main>
@endsection
