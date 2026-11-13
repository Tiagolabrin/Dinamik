@extends('layouts.app')

@section('title', 'Administrar odontólogos')

@section('content')
<main class="container py-4">

    <h2 class="mb-4">Administrar odontólogos</h2>

    @if (session('success'))
    <p class="alert alert-success">{{ session('success') }}</p>
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