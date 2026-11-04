
@extends('layouts.app')

@section('title', 'Inicio')

@section('content')

<div class="container">

    <h1 class="text-center mt-4 mb-3">Bienvenid@</h1>

    <p class="text-center">
        Consultorio odontológico. Odontología general. Endodoncia.
        Ortodoncia. Implantes dentales. Periodoncia.
    </p>

    <p class="text-center mb-4">
        Atención mediante obras sociales.
    </p>

    <nav>
        <div class="row text-center">

            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Reservar un turno</h5>
                        <p class="card-text">
                            Solicitá tu próximo turno odontológico.
                        </p>
                        <a href="{{ route('reserva') }}" class="btn btn-primary">
                            Reservar un turno
                        </a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Mis turnos</h5>
                        <p class="card-text">
                            Consultá tus próximos turnos.
                        </p>
                        <a href="{{ route('mis_turnos') }}" class="btn btn-success">
                            Ver mis turnos
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </nav>

</div>

@endsection
