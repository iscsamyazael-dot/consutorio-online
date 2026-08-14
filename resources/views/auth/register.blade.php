<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Consultorio Online IA</title>

    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body.register-page {
            background:
                linear-gradient(rgba(13,110,253,.85), rgba(0,123,255,.75)),
                url('https://images.unsplash.com/photo-1586773860418-d37222d8fce3');
            background-size: cover;
            background-position: center;
        }

        .register-box .card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,.2);
        }

        .form-control {
            border-radius: 12px;
        }

        .btn-primary,
        .btn-danger {
            border-radius: 12px;
            font-weight: bold;
        }

        .medical-logo {
            font-size: 70px;
            color: #0d6efd;
        }
    </style>
</head>
<body class="register-page d-flex justify-content-center align-items-center my-4">

<div class="register-box" style="width: 420px;">
    <div class="card">
        <div class="card-body register-card-body">

            <div class="text-center mb-4">
                <i class="fas fa-user-plus medical-logo"></i>
                <h2 class="mt-3 font-weight-bold text-primary">Crear Cuenta</h2>
                <p class="text-muted">Registro de Especialista / Médico</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Nombre Completo -->
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-primary text-white">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="form-control"
                        placeholder="Nombre Completo"
                        required
                        autofocus
                        autocomplete="name"
                    >
                </div>

                <!-- Correo Electrónico -->
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-primary text-white">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control"
                        placeholder="Correo Médico"
                        required
                        autocomplete="username"
                    >
                </div>

                <!-- Contraseña -->
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-primary text-white">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Contraseña"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <!-- Confirmar Contraseña -->
                <div class="input-group mb-4">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-primary text-white">
                            <span class="fas fa-check-double"></span>
                        </div>
                    </div>
                    <input
                        type="password"
                        name="password_confirmation"
                        class="form-control"
                        placeholder="Confirmar Contraseña"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <!-- Botón de Registro -->
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-check"></i>
                    Registrar Cuenta
                </button>

                <hr>

                @if (Route::has('google.login'))
                    <a href="{{ route('google.login') }}" class="btn btn-danger btn-block">
                        <i class="fab fa-google"></i>
                        Registrarse con Google
                    </a>
                @endif

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="text-primary">¿Ya tienes cuenta? Iniciar Sesión</a>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>