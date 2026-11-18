
@extends('layouts.app')

@section('title', 'Administrar pacientes')

@section('content')
<main class="container py-4">

    <h2 class="mb-4">Administrar pacientes</h2>

    {{-- Mensajes del sistema --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Email</th>
                    <th>Obra social</th>
                    <th>Fecha de nacimiento</th>
                    <th>Dirección</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($pacientes as $paciente)
                    <tr>
                        <td>{{ $paciente->user->name }}</td>
                        <td>{{ $paciente->user->apellido }}</td>
                        <td>{{ $paciente->user->email }}</td>
                        <td>{{ $paciente->obra_social }}</td>
                        <td>{{ $paciente->fecha_nacimiento }}</td>
                        <td>{{ $paciente->direccion }}</td>
                        <td>
                            <a href="{{ route('admin.pacientes.edit', $paciente) }}"
                                class="btn btn-warning btn-sm">
                                Editar
                            </a>

                            <form action="{{ route('admin.pacientes.destroy', $paciente) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('¿Estás segura de que querés eliminar este paciente?');">

                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-danger btn-sm">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">
                            No hay pacientes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('inicio') }}" class="btn btn-outline-secondary">
        Volver
    </a>

</main>
@endsection
