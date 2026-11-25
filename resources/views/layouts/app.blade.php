
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    @vite('resources/js/app.js')

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --bs-primary: #28566a;
            --bs-primary-rgb: 40, 86, 106;
            --bs-link-color: #376e85;
            --bs-link-color-rgb: 40, 86, 106;
            --bs-link-hover-color: #579ab5;
            --bs-link-hover-color-rgb: 32, 69, 84;
        }

        .btn-primary {
            --bs-btn-bg: var(--bs-primary);
            --bs-btn-border-color: var(--bs-primary);
            --bs-btn-color: #fff;
            --bs-btn-hover-bg: #f0f5f7;
            --bs-btn-hover-color: var(--bs-primary);
            --bs-btn-hover-border-color: var(--bs-primary);
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: #204554;
            --bs-btn-active-border-color: #204554;
            --bs-btn-disabled-bg: var(--bs-primary);
            --bs-btn-disabled-border-color: var(--bs-primary);
            --bs-btn-disabled-color: #fff;
            --bs-btn-focus-shadow-rgb: var(--bs-primary-rgb);
        }

        .btn-outline-primary {
            --bs-btn-bg: transparent;
            --bs-btn-color: var(--bs-primary);
            --bs-btn-border-color: var(--bs-primary);
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: var(--bs-primary);
            --bs-btn-hover-border-color: var(--bs-primary);
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: var(--bs-primary);
            --bs-btn-active-border-color: var(--bs-primary);
            --bs-btn-disabled-color: var(--bs-primary);
            --bs-btn-disabled-border-color: var(--bs-primary);
            --bs-btn-focus-shadow-rgb: var(--bs-primary-rgb);
        }

        body {
            background-color: #f5f8fc;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        .app-content {
            flex: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .app-content > main {
            flex: 1;
            width: 100%;
        }

        .site-footer {
            background-color: #fff;
            border-top: 3px solid var(--bs-primary);
            width: 100%;
            flex-shrink: 0;
        }

        .site-footer h2 {
            color: #28566a;
            font-size: 1.15rem;
            font-weight: 600;
        }

        .insurance-list li {
            color: #28566a;
            margin-bottom: 0.5rem;
        }

        .site-footer a {
            color: #579ab5;
            text-decoration: none;
        }

        .site-footer a:hover {
            color: var(--bs-primary);
            text-decoration: underline;
        }

        .contact-image {
            display: block;
            width: 50px;
            max-width: 100%;
            height: 50px;
            margin: 1rem 0;
        }

        @media (min-width: 768px) {
            .footer-column + .footer-column {
                border-left: 1px solid #dee5ee;
            }
        }
        .logo {
            height: 70px;
            width: auto;
        }
        .logo:hover{
            filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.7));
            transform: scale(0.95);
        }

        .site-header {
            position: relative;
            z-index: 10;
            background-color: var(--bs-primary);
        }

        .header-actions {
            position: relative;
        }

        .notification-inbox-toggle {
            display: inline-flex;
            width: 2.75rem;
            height: 2.75rem;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 50%;
        }

        .notification-inbox-toggle img {
            display: block;
            width: 1.5rem;
            height: 1.5rem;
            object-fit: contain;
        }

        .notification-inbox {
            position: absolute;
            top: calc(100% + 0.75rem);
            right: 0;
            z-index: 20;
            width: min(24rem, calc(100vw - 2rem));
            max-height: min(70vh, 32rem);
            overflow-y: auto;
            color: #212529;
            background: #fff;
            border: 4px solid #dee2e6;
            border-radius: 0.75rem;
            box-shadow: 0 0.75rem 2rem rgb(0 0 0 / 18%);
        }

        .notification-item + .notification-item {
            border-top: 1px solid #dee2e6;
        }

        .notification-item.unread {
            background-color: #f0f6f8;
        }
    </style>
</head>

<body>

<header class="site-header text-white py-1 px-4">
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('inicio') }}">
                <img src="{{ asset('images/blanco.consul.png') }}"
                    alt="Logo del consultorio"
                    class="logo">
            </a>

            <div class="header-actions d-flex flex-wrap justify-content-end align-items-center gap-3">
                <h1 class="mb-0 text-end fs-5">
                    Consultorio Odontológico
                </h1>

                @auth
                    @if (in_array(auth()->user()->tipo_usuario, ['paciente', 'odontologo'], true))
                        <button
                            type="button"
                            id="notification-inbox-toggle"
                            class="btn btn-outline-light btn-sm notification-inbox-toggle"
                            aria-label="Abrir notificaciones"
                            aria-expanded="false"
                            aria-controls="notification-inbox-panel"
                        >
                            <img src="{{ asset('images/notificacion.png') }}" alt="">
                        </button>

                        <section
                            id="notification-inbox-panel"
                            class="notification-inbox p-3"
                            aria-label="Últimas notificaciones"
                            hidden
                        >
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                <h2 class="h6 mb-0">Últimas notificaciones</h2>
                                <span id="notification-inbox-count" class="badge text-bg-secondary"></span>
                            </div>
                            <p id="notification-inbox-status" class="small text-body-secondary mb-2" role="status" aria-live="polite"></p>
                            <div id="notification-inbox-list"></div>
                        </section>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    <div class="app-content">
        @yield('content')
    </div>

    @auth
        @if (in_array(auth()->user()->tipo_usuario, ['paciente', 'odontologo'], true))
            <div
                data-unread-notifications-url="{{ route('notificaciones.no-leidas') }}"
                data-read-notifications-url="{{ route('notificaciones.leidas') }}"
                data-recent-notifications-url="{{ route('notificaciones.recientes') }}"
                hidden
            ></div>
        @endif
    @endauth

    <footer class="site-footer py-4">
        <div class="container">
            <div class="row justify-content-center g-4 text-start">

                <section class="footer-column col-md-6 px-md-4"
                        aria-labelledby="insurance-heading">

                    <h2 id="insurance-heading" class="mb-3">
                        Obras sociales
                    </h2>

                    <ul class="insurance-list list-unstyled mb-0">
                        <li>• ISSN</li>
                        <li>• SWISS MEDICAL</li>
                        <li>• FEDERADA SALUD</li>
                        <li>• OSDE</li>
                        <li>• SANCOR SALUD</li>
                        <li>• OSPEPRI</li>
                        <li>• OSDEPYM</li>
                    </ul>
                </section>

                <section class="footer-column col-md-6 px-md-4"
                         aria-labelledby="contact-heading">

                    <h2 id="contact-heading" class="mb-3">
                        Información de contacto
                    </h2>

                    <address class="mb-0">
                        <p class="mb-2">
                            <a href="mailto:odontdrlupianez@gmail.com">
                                odontdrlupianez@gmail.com
                            </a>
                        </p>

                        <p class="mb-2">
                            <a href="tel:+54299154609519">
                                +54 (299) 154609519
                            </a>
                        </p>

                        <p class="mb-0">
                            Ameghino 337, Neuquén Capital.
                        </p>

                        <img class="contact-image"
                            src="{{ asset('images/consul.png') }}"
                            alt="Consultorio">
                    </address>
                </section>

            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
