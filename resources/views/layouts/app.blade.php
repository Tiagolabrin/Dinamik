<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @yield('title')
    </title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f8fc;
        }

        .app-content {
            flex: 1;
            min-height: calc(100vh - 5rem);
        }

        .site-footer {
            background-color: #fff;
            border-top: 3px solid #0d6efd;
        }

        .site-footer h2 {
            color: #17365d;
            font-size: 1.15rem;
            font-weight: 600;
        }

        .insurance-list li {
            color: #334155;
            margin-bottom: 0.5rem;
        }

        .site-footer a {
            color: #334155;
            text-decoration: none;
        }

        .site-footer a:hover {
            color: #0d6efd;
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

    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="bg-primary text-white py-1 px-4">
        <header class="d-flex justify-content-between align-items-center py-0">
            <a href="http://127.0.0.1:8000/">
                <img src="{{ asset('images/consul.png') }}" alt="Logo del consultorio" class="logo">
            </a>
            <h1 class="mb-0 text-end fs-5">Consultorio Odontológico</h1>
        </header>
    </header>
    <div class="app-content">
        @yield('content')
    </div>

    <footer class="site-footer mt-5 py-4">
        <div class="container">
            <div class="row justify-content-center g-4 text-start">
                <section class="footer-column col-md-6 px-md-4" aria-labelledby="insurance-heading">
                    <h2 id="insurance-heading" class="mb-3">Obras sociales</h2>
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

                <section class="footer-column col-md-6 px-md-4" aria-labelledby="contact-heading">
                    <h2 id="contact-heading" class="mb-3">Información de contacto</h2>
                    <address class="mb-0">
                        <p class="mb-2">
                            <a href="mailto:odontdrlupianez@gmail.com">odontdrlupianez@gmail.com</a>
                        </p>
                        <p class="mb-2">
                            <a href="tel:+54299154609519">+54 (299) 154609519</a>
                        </p>
                        <p class="mb-0">Ameghino 337, Neuquén Capital.</p>
                        <img class="contact-image" src="{{ asset('images/consul.png') }}" alt="Consultorio">
                    </address>
                </section>
            </div>
        </div>
    </footer>

     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>