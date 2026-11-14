@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<style>
    .app-content {
        min-height: auto;
    }
</style>
<div class="container">
    @auth

    <h1 class="text-center mt-4 mb-3">Bienvenid@</h1>

    <p class="text-center">
        Consultorio odontológico. Odontología general. Endodoncia.
        Ortodoncia. Implantes dentales. Periodoncia.
    </p>

    <p class="text-center mb-4">
        Atención mediante obras sociales.
    </p>

    <nav>
        <div class="row text-center g-3">
            @if (auth()->user()->tipo_usuario === 'paciente')
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Mis turnos</h5>
                        <p class="card-text">Consultá tus próximos turnos.</p>
                        <a href="{{ route('mis_turnos') }}" class="btn btn-success">
                            Ver mis turnos
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Reservar un turno</h5>
                        <p class="card-text">Solicitá tu próximo turno odontológico.</p>
                        <a href="{{ route('reserva') }}" class="btn btn-primary">
                            Reservar un turno
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Mi perfil</h5>
                        <p class="card-text">Consultá tus datos personales.</p>
                        <a href="{{ route('perfil') }}" class="btn btn-outline-primary">
                            Ver mi perfil
                        </a>
                    </div>
                </div>
            </div>
            @elseif (auth()->user()->tipo_usuario === 'odontologo')
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Mi agenda</h5>
                        <p class="card-text">Consultá los turnos asignados.</p>
                        <a href="{{ route('odontologo.agenda') }}" class="btn btn-primary">
                            Ver mi agenda
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Mi perfil</h5>
                        <p class="card-text">Consultá tus datos y disponibilidad.</p>
                        <a href="{{ route('perfil') }}" class="btn btn-outline-primary">
                            Ver mi perfil
                        </a>
                    </div>
                </div>
            </div>

            @elseif (auth()->user()->tipo_usuario === 'administrador')


            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Odontólogos</h5>
                        <p class="card-text">Administrá las cuentas de los odontólogos.</p>
                        <a href="{{ route('admin.odontologos.index') }}" class="btn btn-primary">
                            Ver odontólogos
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Consultorio</h5>
                        <p class="card-text">Consultá y modificá los datos del consultorio.</p>
                        <a href="{{ route('admin.consultorio.edit') }}" class="btn btn-primary">
                            Administrar consultorio
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <h5 class="card-title">Pacientes</h5>

                        <p class="card-text">
                            Consultá y modificá los datos de los pacientes.
                        </p>

                        <a href="{{ route('admin.pacientes.index') }}"
                            class="btn btn-primary">
                            Ver pacientes
                        </a>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </nav>

    <div class="text-center mt-4">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">
                Cerrar sesión
            </button>
        </form>
    </div>

</div>

@else
<main class="container py-4">
    <div class="row g-4">
        <section class="col-md-7">
            <br>
            <h2>Conocé nuestro consultorio</h2><br>
            <p> Hace más de 25 años que trabajamos por la salud bucal de nuestra comunidad, a través del Círculo Odontológico de Neuquén.</p>
            <p>Nos especializamos en odontología general y brindamos atención integral en distintas áreas, como endodoncia, ortodoncia, implantes dentales y periodoncia</p>
            <p>Nuestro compromiso es ofrecer una atención personalizada y de calidad, acompañando a cada paciente en el cuidado de su salud bucal.</p>
            <p>Trabajamos con obras sociales.</p>
        </section>

        <section class="col-md-5">
            <div class="card bg-white">
                <div class="card-body">
                    <h2 class="card-title">Iniciar sesión</h2>

                    @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        Revisá el email y la contraseña e intentá nuevamente.
                    </div>
                    @endif

                    <form action="{{ route('login.submit') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control bg-light"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                placeholder="usuario@gmail.com"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control bg-light"
                                autocomplete="current-password"
                                placeholder="**********"
                                required>
                        </div>

                        <button type="submit" class="btn btn-primary">Ingresar</button>
                    </form>

                    <p class="mt-3 mb-0">
                        ¿Todavía no tenés cuenta?
                        <a href="{{ route('registro') }}">Registrate como paciente</a>
                    </p>
                </div>
            </div>
        </section>
    </div>
</main>

@endauth
@endsection