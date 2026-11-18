
@extends('layouts.app')

@section('title', 'Administrar odontólogos')

@section('content')
<main class="container py-4">

    <h2 class="mb-4">Administrar odontólogos</h2>

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

    <a href="{{ route('admin.odontologos.registro') }}"
        class="btn btn-primary mb-3">
        Crear odontólogo
    </a>

    @if ($odontologos->isEmpty())

        <p>No hay odontólogos registrados.</p>

    @else

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Matrícula</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($odontologos as $odontologo)
                        <tr>
                            <td>{{ $odontologo->user->name }}</td>
                            <td>{{ $odontologo->user->apellido }}</td>
                            <td>{{ $odontologo->user->email }}</td>
                            <td>{{ $odontologo->matricula }}</td>
                            <td>
                                <a href="{{ route('admin.odontologos.edit', $odontologo) }}"
                                    class="btn btn-warning btn-sm">
                                    Editar
                                </a>

                                <form action="{{ route('admin.odontologos.destroy', $odontologo) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('¿Estás segura de que querés eliminar este odontólogo?');">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @endif

    <a href="{{ route('inicio') }}" class="btn btn-outline-secondary">
        Volver
    </a>

</main>
@endsection
