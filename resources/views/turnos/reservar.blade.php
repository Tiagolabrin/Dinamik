@extends('layouts.app')

@section('title', 'Reservá tu turno')

@section('content')

<h1>Reservar un turno</h1>

<form action=" " method="POST">
    @csrf

    @if ($errors->any())
        <div role="alert">
            <p>Revisá estos datos:</p>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <label for="odontologo">Selecciona un odontólogo</label>
    <select name="odontologo_id" id="odontologo" required>
        <option value="">Selecciona un odontólogo</option>

        @foreach ($odontologos as $odontologo)
            <option value="{{ $odontologo->id }}" @selected(old('odontologo_id') == $odontologo->id)>
                {{ $odontologo->user->name }} {{ $odontologo->user->apellido }}
            </option>
        @endforeach
    </select>

    @error('odontologo_id')
        <p role="alert">{{ $message }}</p>
    @enderror

    <label for="fecha">Elegí la fecha</label>
    <input
        type="date"
        name="fecha"
        id="fecha"
        min="{{ now()->toDateString() }}"
        value="{{ old('fecha') }}"
        required
    >

    @error('fecha')
        <p role="alert">{{ $message }}</p>
    @enderror

    <section class="calendar" aria-labelledby="mes-calendario">
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

    <label for="horario">Elegí un horario</label>
    <select name="horario" id="horario" required disabled>
        <option value="">Elegí odontólogo y fecha</option>
    </select>

    <p id="mensaje-horarios" role="status" aria-live="polite"></p>

    @error('horario')
        <p role="alert">{{ $message }}</p>
    @enderror

    <label for="observaciones">Observaciones</label>
    <textarea id="observaciones" name="observaciones" placeholder="(Opcional)">{{ old('observaciones') }}</textarea>

    @error('observaciones')
        <p role="alert">{{ $message }}</p>
    @enderror

    <button type="submit">Confirmar reserva</button>
</form>

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
    const inputFecha = document.getElementById('fecha');
    const selectHorario = document.getElementById('horario');
    const mensajeHorarios = document.getElementById('mensaje-horarios');
    const urlHorarios = @json(route('reserva.horarios'));

    const tituloMes = document.getElementById('mes-calendario');
    const diasCalendario = document.getElementById('dias-calendario');
    const botonMesAnterior = document.getElementById('mes-anterior');
    const botonMesSiguiente = document.getElementById('mes-siguiente');

    function fechaLocal(fecha){
        const [anio, mes, dia] = fecha.split('-').map(Number);
        return new Date(anio, mes - 1, dia);
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

    async function cargarHorarios() {
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
            selectHorario.replaceChildren(new Option('Error al cargar horarios', ''));
            mensajeHorarios.textContent = 'No se pudieron cargar los horarios. Probá de nuevo.';
        }
    }

    selectOdontologo.addEventListener('change', cargarHorarios);
    inputFecha.addEventListener('change', cargarHorarios);

    if (selectOdontologo.value && inputFecha.value) {
        cargarHorarios();
    }
</script>

@endsection
