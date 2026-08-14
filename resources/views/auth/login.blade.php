<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Consultorio Online IA</title>

    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body.login-page {
            background:
                linear-gradient(rgba(13,110,253,.85), rgba(0,123,255,.75)),
                url('https://images.unsplash.com/photo-1586773860418-d37222d8fce3');
            background-size: cover;
            background-position: center;
        }

        .login-box .card {
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
<body class="login-page">

<div class="login-box">
    <div class="card">
        <div class="card-body login-card-body">

            <div class="text-center mb-4">
                <i class="fas fa-hospital-user medical-logo"></i>
                <h2 class="mt-3 font-weight-bold text-primary">Consultorio Online IA</h2>
                <p class="text-muted">Plataforma Inteligente de Atención Médica</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="input-group mb-4">
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
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="input-group mb-4">
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
                        autocomplete="current-password"
                    >
                </div>

                <div class="row mb-3">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember">Mantener sesión</label>
                        </div>
                    </div>

                    <div class="col-4 text-right">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-primary">¿Olvidaste tu contraseña?</a>
                        @endif
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </button>

                <hr>

                @if (Route::has('google.login'))
                    <a href="{{ route('google.login') }}" class="btn btn-danger btn-block">
                        <i class="fab fa-google"></i>
                        Login con Google
                    </a>
                @endif

                @if (Route::has('register'))
                    <div class="text-center mt-3">
                        <a href="{{ route('register') }}" class="text-primary">Crear cuenta médica</a>
                    </div>
                @endif
            </form>

        </div>
    </div>
</div>

</body>
</html>

