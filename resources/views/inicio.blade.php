@extends('layouts.app')

@section('title','Inicio')

@section('content')

<h1>Bienvenid@</h1>

<!-- <h2> breve descripcion -->
<nav>
    <a href="{{ route('reserva') }}">Reservar un turno</a>
    <a href="{{ route('mis_turnos') }}">Mis turnos</a>

</nav>

@endsection