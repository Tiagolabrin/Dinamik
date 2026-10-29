@php
    $month = now()->startOfMonth();
    $daysInMonth = $month->daysInMonth;
    $offset = $month->dayOfWeek; // La semana empieza en domingo
    $selectedDay = (int) request('day', now()->day);

    $appointmentsByDay = $appointments->groupBy(
        fn ($appointment) => $appointment->starts_at->day
    );
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agenda dental</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #f4f3f0;
            color: #26332f;
            font-family: Arial, sans-serif;
        }

        .calendar {
            width: min(100%, 430px);
            padding: 22px 20px 14px;
            background: #fff;
            border: 1px solid #eae8e3;
            border-radius: 30px;
            box-shadow: 0 14px 35px #26332f12;
        }

        .calendar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 19px;
        }

        .month {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 19px;
            font-weight: 700;
        }

        .month-icon {
            color: #ed7054;
            font-size: 19px;
        }

        .repeat {
            padding: 7px 10px;
            border-radius: 20px;
            background: #f1f1ed;
            color: #626a65;
            font-size: 11px;
            white-space: nowrap;
        }

        .weekdays,
        .days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            justify-items: center;
            align-items: center;
        }

        .weekdays {
            margin-bottom: 5px;
            color: #a0a39f;
            font-size: 10px;
            text-transform: uppercase;
        }

        .days {
            row-gap: 7px;
        }

        .day {
            width: 37px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            color: #35413c;
            font-size: 12px;
        }

        .day.has-appointment {
            background: #e4edf0;
            font-weight: 700;
        }

        .day.selected {
            background: #f15d3c;
            color: white;
            font-weight: 700;
            box-shadow: 0 4px 9px #f15d3c38;
        }

        .appointment {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 20px;
            padding: 12px 14px;
            border-radius: 18px;
            background: #f3f2ee;
            font-size: 12px;
        }

        .appointment-time {
            white-space: nowrap;
        }

        .status {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #258d68;
            white-space: nowrap;
        }

        .check {
            width: 17px;
            height: 17px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #d9eee5;
            font-size: 11px;
            font-weight: 700;
        }

        .clock-button {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            margin: 10px 0 0 auto;
            border: 1px solid #eeeae3;
            border-radius: 50%;
            background: #fff;
            color: #ed7054;
            box-shadow: 0 4px 12px #26332f12;
            font-size: 20px;
        }

        @media (max-width: 380px) {
            .calendar {
                padding-inline: 12px;
            }

            .day {
                width: 32px;
            }

            .appointment {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <main class="calendar">
        <header class="calendar-header">
            <div class="month">
                <span class="month-icon">▦</span>
                <span>{{ $month->format('F Y') }}</span>
            </div>
            <div class="repeat">⟳ &nbsp;weekly · 4×</div>
        </header>

        <section class="weekdays" aria-label="Días de la semana">
            <span>Sun</span><span>Mon</span><span>Tue</span>
            <span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
        </section>

       <section class="days" aria-label="Calendario">
    @for ($i = 0; $i < $offset; $i++)
        <span class="day"></span>
    @endfor

    @for ($day = 1; $day <= $daysInMonth; $day++)
        @php
            $hasAppointment = $appointmentsByDay->has($day);
            $isSelected = $day === $selectedDay;
        @endphp

        <a
            class="day {{ $hasAppointment ? 'has-appointment' : '' }} {{ $isSelected ? 'selected' : '' }}"
            href="?day={{ $day }}"
            style="text-decoration: none;"
        >
            {{ $day }}
        </a>
    @endfor
</section>


<section class="appointment-list">
    @forelse ($appointmentsByDay->get($selectedDay, collect()) as $appointment)
        <article class="appointment">
            <span class="appointment-time">
                {{ $appointment->starts_at->format('D j · H:i') }}
                –
                {{ $appointment->ends_at->format('H:i') }}
                · {{ $appointment->title }}
            </span>
            <span class="status">✓ Cita</span>
        </article>
    @empty
        <p>No hay citas para este día.</p>
    @endforelse
</section>
        <button class="clock-button" type="button" aria-label="Añadir cita">◷</button>
    </main>
</body>
</html>
