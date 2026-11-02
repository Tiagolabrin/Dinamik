@extends('layouts.app')

@section('content')
    <h2>Crear cuenta</h2>

    <form action="" method="POST">
        @csrf 

        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" required placeholder="Nombre">

        <label for="apellido">Apellido</label>
        <input type="text" id="apellido" name="apellido" required placeholder="Apellido">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com">

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        <label for="password">Confirmar contraseña</label>
        <input type="password" id="password_confir" name="password_confir" required>

        <label for="obra_social">Obra social</label>
        <input type="text" id="obra_social" name="obra_social" placeholder="ISSN">

        <label for="fecha_nacimiento">Fecha de nacimiento</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>

        <label for="direccion">Dirección</label>
        <input type="text" id="direccion" name="direccion" required>

        <button type="submit">Registrar</button>
    </form>

@endsection