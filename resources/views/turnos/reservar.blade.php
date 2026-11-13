@extends('layouts.app')

@section('title', 'Reservá tu turno')

@section('content')

<main class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-3">Reservar un turno</h1>
            <p class="text-body-secondary">
                Elegí el odontólogo, la fecha y uno de los horarios disponibles.
            </p>

            @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if ($odontologos->isEmpty())
                <div class="alert alert-info" role="status">
                    Todavía no hay odontólogos disponibles para reservar.
                </div>
            @else
                <form action="{{ route('reserva.store') }}" method="POST" class="card">
                    @csrf

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <p class="mb-2">Revisá los datos del formulario.</p>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="odontologo" class="form-label">Odontólogo</label>
                            <select
                                name="odontologo_id"
                                id="odontologo"
                                class="form-select"
                                required
                            >
                                <option value="">Seleccioná un odontólogo</option>
                                @foreach ($odontologos as $odontologo)
                                    <option
                                        value="{{ $odontologo->id }}"
                                        @selected(old('odontologo_id') == $odontologo->id)
                                    >
                                        {{ $odontologo->user->name }} {{ $odontologo->user->apellido }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <input
                            type="hidden"
                            name="fecha"
                            id="fecha"
                            value="{{ old('fecha') }}"
                        >
                        <p id="fecha-seleccionada" class="form-text" role="status" aria-live="polite"></p>

                        <section id="seccion-calendario" class="calendar" aria-labelledby="mes-calendario" hidden>
                            <header class="calendar-header">
                                <button type="button" id="mes-anterior" aria-label="Mes anterior">←</button>
                                <h2 id="mes-calendario"></h2>
                                <button type="button" id="mes-siguiente" aria-label="Mes siguiente">→</button>
                            </header>

                            <div class="weekdays" aria-hidden="true">
                                <span>Lun</span>
                                <span>Mar</span>
                                <span>Mié</span>
                                <span>Jue</span>
                                <span>Vie</span>
                                <span>Sáb</span>
                                <span>Dom</span>
                            </div>

                            <div id="dias-calendario" class="days" aria-label="Elegí una fecha"></div>
                        </section>

                        <section id="seccion-horarios" hidden>
                            <div class="mb-3">
                                <label for="horario" class="form-label">Horario disponible</label>
                                <select
                                    name="horario"
                                    id="horario"
                                    class="form-select"
                                    required
                                    disabled
                                >
                                    <option value="">Elegí odontólogo y fecha</option>
                                </select>
                                <div id="mensaje-horarios" class="form-text" role="status" aria-live="polite"></div>
                            </div>
                        </section>

                        <section id="seccion-confirmacion" hidden>
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea
                                    id="observaciones"
                                    name="observaciones"
                                    class="form-control"
                                    rows="3"
                                    maxlength="1000"
                                    placeholder="Opcional"
                                >{{ old('observaciones') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Confirmar reserva
                            </button>
                        </section>
                    </div>
                </form>
            @endif
        <div class="text-center mt-3"><a href="{{ route('inicio') }}" class="btn btn-outline-secondary">Inicio</a></div>
        </div>
    </div>
</main>
<style>
    .calendar {
        max-width: 430px;
        margin-block: 24px;
        padding: 22px 20px;
        border: 1px solid #eae8e3;
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 12px 30px #26332f12;
    }

    .calendar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .calendar-header h2 {
        margin: 0;
        font-size: 19px;
        text-transform: capitalize;
    }

    .weekdays,
    .days {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        justify-items: center;
        align-items: center;
    }

    .weekdays {
        margin-bottom: 8px;
        color: #777;
        font-size: 12px;
        font-weight: 700;
    }

    .days {
        row-gap: 7px;
    }

    .calendar-header button,
    .days button {
        border: 0;
        border-radius: 50%;
        cursor: pointer;
        font: inherit;
    }

    .calendar-header button {
        width: 36px;
        height: 36px;
        background: #f3f2ee;
        font-size: 20px;
    }

    .calendar-header button:disabled {
        cursor: not-allowed;
        opacity: 0.4;
    }

    .days button {
        width: 36px;
        height: 36px;
        background: transparent;
    }

    .days button:hover:not(:disabled) {
        background: #fbe4dd;
    }

    .days button.selected {
        background: #ed7054;
        color: #fff;
        font-weight: 700;
    }

    .days button:disabled {
        color: #b6bbb7;
        cursor: not-allowed;
    }

    @media (max-width: 400px) {
        .calendar {
            padding-inline: 12px;
        }

        .days button {
            width: 32px;
            height: 32px;
        }
    }
</style>

<script>
    const selectOdontologo = document.getElementById('odontologo');
    const seccionCalendario = document.getElementById('seccion-calendario');
    const seccionHorarios = document.getElementById('seccion-horarios');
    const seccionConfirmacion = document.getElementById('seccion-confirmacion');
    const fechaSeleccionada = document.getElementById('fecha-seleccionada');
    const inputFecha = document.getElementById('fecha');
    const selectHorario = document.getElementById('horario');
    const mensajeHorarios = document.getElementById('mensaje-horarios');
    const urlHorarios = @json(route('reserva.horarios'));
    let solicitudHorarios = 0;

    const tituloMes = document.getElementById('mes-calendario');
    const diasCalendario = document.getElementById('dias-calendario');
    const botonMesAnterior = document.getElementById('mes-anterior');
    const botonMesSiguiente = document.getElementById('mes-siguiente');

    function fechaLocal(fecha){
        const [anio, mes, dia] = fecha.split('-').map(Number);
        return new Date(anio, mes - 1, dia);
    }

    function mostrarFechaSeleccionada(){
        if (!inputFecha.value) {
            fechaSeleccionada.textContent='';
            return;
        }

        fechaSeleccionada.textContent = `Fecha elegida: ${
            new Intl.DateTimeFormat('es-AR', {dateStyle:'long' }).format(fechaLocal(inputFecha.value))
        }`;
    }
    const fechaInicial = inputFecha.value ? fechaLocal(inputFecha.value) : new Date();

    let mesVisible = new Date( fechaInicial.getFullYear() , fechaInicial.getMonth() , 1 );

    function renderizarCalendario() {
        const anio = mesVisible.getFullYear();
        const mes = mesVisible.getMonth();
        const primerDia = new Date(anio, mes, 1);
        const diaDelMes = new Date(anio, mes + 1, 0).getDate();
        const espaciosIniciales= (primerDia.getDay() + 6 ) % 7;

        tituloMes.textContent = new Intl.DateTimeFormat('es-AR', {
            month: 'long',
            year: 'numeric'
        }).format(mesVisible);

        diasCalendario.replaceChildren();

        for (let espacio = 0; espacio < espaciosIniciales; espacio ++) {
            const celdaVacia = document.createElement('span');
            celdaVacia.setAttribute('aria-hidden', 'true');
            diasCalendario.append(celdaVacia);
        }

        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        for (let dia = 1; dia <= diaDelMes; dia++) {
            const fecha = new Date(anio, mes, dia);
            const diaSemana = fecha.getDay();
            const esFinDeSemana = diaSemana === 0 || diaSemana === 6;
            const fechaTexto = [
                anio,
                String(mes + 1).padStart(2, '0'),
                String(dia).padStart(2, '0'),
            ].join('-');

            const botonDia = document.createElement('button');
            botonDia.type = "button";
            botonDia.textContent = dia;
            botonDia.disabled= fecha < hoy || esFinDeSemana;
            botonDia.setAttribute('aria-pressed', String(inputFecha.value === fechaTexto));

            if (inputFecha.value === fechaTexto) {
                botonDia.classList.add('selected');
            }

            botonDia.addEventListener('click', () => {
                inputFecha.value = fechaTexto;
                mostrarFechaSeleccionada();
                renderizarCalendario();
                inputFecha.dispatchEvent(new Event('change', { bubbles: true}));
            });

            diasCalendario.append(botonDia);
        }

        const primerDiaDelMesActual = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        botonMesAnterior.disabled = mesVisible <= primerDiaDelMesActual;
    }

    botonMesAnterior.addEventListener('click', () => {
        mesVisible = new Date(mesVisible.getFullYear(), mesVisible.getMonth() - 1, 1);
        renderizarCalendario();
    });

    botonMesSiguiente.addEventListener('click', () => {
        mesVisible = new Date(mesVisible.getFullYear(), mesVisible.getMonth() + 1, 1);
        renderizarCalendario();
    });

    renderizarCalendario();
    mostrarFechaSeleccionada();

    async function cargarHorarios() {
        seccionConfirmacion.hidden = true;
        seccionHorarios.hidden = !(selectOdontologo.value && inputFecha.value);

        const solicitudActual = ++solicitudHorarios;
        selectHorario.disabled = true;
        mensajeHorarios.textContent = '';

        if (!selectOdontologo.value || !inputFecha.value) {
            selectHorario.replaceChildren(new Option('Elegí odontólogo y fecha', ''));
            return;
        }

        selectHorario.replaceChildren(new Option('Buscando horarios…', ''));

        try {
            const url = new URL(urlHorarios, window.location.origin);
            url.searchParams.set('odontologo_id', selectOdontologo.value);
            url.searchParams.set('fecha', inputFecha.value);

            const respuesta = await fetch(url, {
                headers: { Accept: 'application/json' },
            });

            if (!respuesta.ok) {
                throw new Error('No se pudieron cargar los horarios.');
            }

            const datos = await respuesta.json();
            if (solicitudActual !== solicitudHorarios) return;

            selectHorario.replaceChildren(new Option('Seleccioná un horario', ''));
            for (const hora of datos.horarios) {
                selectHorario.add(new Option(hora, hora));
            }

            if (datos.horarios.length === 0) {
                mensajeHorarios.textContent = 'No hay horarios disponibles para esa fecha.';
                return;
            }

            selectHorario.disabled = false;
        } catch (error) {
            if (solicitudActual !== solicitudHorarios) return;
            selectHorario.replaceChildren(new Option('Error al cargar horarios', ''));
            mensajeHorarios.textContent = 'No se pudieron cargar los horarios. Probá de nuevo.';
        }
    }

    selectOdontologo.addEventListener('change', () => {
        inputFecha.value = "";
        mostrarFechaSeleccionada();
        seccionCalendario.hidden = !selectOdontologo.value;
        seccionConfirmacion.hidden = true;
        seccionHorarios.hidden = true;
        selectHorario.disabled = true;
        selectHorario.replaceChildren(new Option('Elegí una fecha', ''));
        renderizarCalendario();
    });

    inputFecha.addEventListener('change', () => {
        seccionConfirmacion.hidden = true;
        selectHorario.value = '';
        cargarHorarios();
    });

    selectHorario.addEventListener('change', () => {
        seccionConfirmacion.hidden = !( selectOdontologo.value && inputFecha.value && selectHorario.value);
    });

    seccionCalendario.hidden = !selectOdontologo.value;
    seccionHorarios.hidden = true;
    seccionConfirmacion.hidden = true;

    if (selectOdontologo.value && inputFecha.value) {
        cargarHorarios();
    }
</script>
@endsection
