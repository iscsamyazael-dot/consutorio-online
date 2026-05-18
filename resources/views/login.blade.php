<!DOCTYPE html>
<html>
<head>
    <title>Login Consultorio</title>
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
</head>

<body class="login-page">

<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <b>Consultorio</b> Online
        </div>

        <div class="card-body">

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="input-group mb-3">
                    <input type="email" name="email"
                        class="form-control"
                        placeholder="Correo">
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password"
                        class="form-control"
                        placeholder="Password">
                </div>

                <button class="btn btn-primary btn-block">
                    Iniciar sesión
                </button>

            </form>

            <hr>

            <a href="{{ route('google.login') }}"
               class="btn btn-danger btn-block">
               Login con Google
            </a>

        </div>
    </div>
</div>

</body>
</html>