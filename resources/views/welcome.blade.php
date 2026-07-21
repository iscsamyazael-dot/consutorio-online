<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultorio Online Inteligente</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        body{
            background:#f4f8fb;
            font-family:Arial, Helvetica, sans-serif;
        }

        .navbar{
            background:white;
            box-shadow:0 2px 10px rgba(0,0,0,.05);
        }

        .hero{
            min-height:90vh;
            display:flex;
            align-items:center;
        }

        .hero-title{
            font-size:60px;
            font-weight:bold;
            color:#0d6efd;
        }

        .hero-text{
            font-size:20px;
            color:#555;
        }

        .btn-main{
            background:#0d6efd;
            color:white;
            padding:12px 30px;
            border-radius:12px;
            text-decoration:none;
            transition:.3s;
        }

        .btn-main:hover{
            background:#0b5ed7;
            color:white;
        }

        .section{
            padding:100px 0;
        }

        .card-service{
            background:white;
            border:none;
            border-radius:20px;
            padding:30px;
            transition:.3s;
            box-shadow:0 5px 20px rgba(0,0,0,.05);
        }

        .card-service:hover{
            transform:translateY(-10px);
        }

        .icon-service{
            font-size:50px;
            color:#0d6efd;
        }

        .chat-button{
            position:fixed;
            bottom:30px;
            right:30px;
            width:65px;
            height:65px;
            border-radius:50%;
            background:#0d6efd;
            color:white;
            border:none;
            font-size:28px;
            box-shadow:0 5px 15px rgba(0,0,0,.2);
        }

        footer{
            background:#0d6efd;
            color:white;
            padding:30px 0;
        }

    </style>

</head>
<body>

{{-- NAVBAR --}}
<nav class="navbar navbar-expand-lg py-3">
    <div class="container">

        <a class="navbar-brand fw-bold" href="#">
            <i class="bi bi-heart-pulse-fill text-primary"></i>
            Consultorio Online
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menu">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse" id="menu">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item me-3">
                    <a class="nav-link" href="#">Inicio</a>
                </li>

                <li class="nav-item me-3">
                    <a class="nav-link" href="#">Servicios</a>
                </li>

                <li class="nav-item me-3">
                    <a class="nav-link" href="#">Especialidades</a>
                </li>

                <li class="nav-item me-3">
                    <a class="nav-link" href="#">Contacto</a>
                </li>

                <li class="nav-item me-2">
                    <a href="/login" class="btn btn-outline-primary">
                        Iniciar sesión
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/register" class="btn btn-primary">
                        Registrarse
                    </a>
                </li>

            </ul>

        </div>

    </div>
</nav>

{{-- HERO --}}
<section class="hero">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-6">

                <h1 class="hero-title">
                    Atención Médica Inteligente 24/7
                </h1>

                <p class="hero-text my-4">
                    Consulta médicos, agenda citas y recibe atención médica desde cualquier lugar.
                </p>

                <div class="d-flex gap-3">

                    <a href="#" class="btn-main">
                        Agendar Consulta
                    </a>

                    <a href="/login" class="btn btn-outline-primary btn-lg">
                        Iniciar sesión
                    </a>

                </div>

            </div>

            <div class="col-lg-6 text-center">

                <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef"
                     class="img-fluid rounded-4 shadow"
                     alt="doctor">

            </div>

        </div>

    </div>

</section>

{{-- SERVICIOS --}}
<section class="section">

    <div class="container">

        <div class="text-center mb-5">

            <h2 class="fw-bold">
                Nuestros Servicios
            </h2>

            <p class="text-muted">
                Tecnología moderna para atención médica eficiente
            </p>

        </div>

        <div class="row g-4">

            <div class="col-md-4">

                <div class="card-service text-center h-100">

                    <i class="bi bi-camera-video icon-service"></i>

                    <h4 class="mt-4">
                        Videoconsultas
                    </h4>

                    <p class="text-muted">
                        Consulta con médicos desde casa.
                    </p>

                </div>

            </div>

            <div class="col-md-4">

                <div class="card-service text-center h-100">

                    <i class="bi bi-robot icon-service"></i>

                    <h4 class="mt-4">
                        IA Médica
                    </h4>

                    <p class="text-muted">
                        Asistente inteligente para orientación clínica.
                    </p>

                </div>

            </div>

            <div class="col-md-4">

                <div class="card-service text-center h-100">

                    <i class="bi bi-file-earmark-medical icon-service"></i>

                    <h4 class="mt-4">
                        Expediente Digital
                    </h4>

                    <p class="text-muted">
                        Historial médico seguro y accesible.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

{{-- IA SECTION --}}
<section class="section bg-white">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-6">

                <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158"
                     class="img-fluid rounded-4 shadow">

            </div>

            <div class="col-lg-6">

                <h2 class="fw-bold mb-4">
                    Inteligencia Artificial Médica
                </h2>

                <p class="text-muted fs-5">
                    Nuestro sistema utiliza inteligencia artificial para apoyar el análisis clínico y mejorar la experiencia del paciente.
                </p>

                <ul class="list-group list-group-flush">

                    <li class="list-group-item">
                        ✅ Detección de síntomas
                    </li>

                    <li class="list-group-item">
                        ✅ Orientación médica
                    </li>

                    <li class="list-group-item">
                        ✅ Respuestas rápidas
                    </li>

                    <li class="list-group-item">
                        ✅ Atención 24/7
                    </li>

                </ul>

            </div>

        </div>

    </div>

</section>

{{-- FOOTER --}}
<footer>

    <div class="container text-center">

        <h5>
            Consultorio Online Inteligente
        </h5>

        <p class="mb-0">
            © 2026 Todos los derechos reservados
        </p>

    </div>

</footer>

{{-- CHAT BUTTON --}}
<button class="chat-button">

    <i class="bi bi-chat-dots-fill"></i>

</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>