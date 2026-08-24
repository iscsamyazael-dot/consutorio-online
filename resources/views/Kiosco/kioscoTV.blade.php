{{-- resources/views/kiosco/index.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
     <meta name="csrf-token" content="{{ csrf_token() }}">
     <meta name="base-url" content="{{ url('/') }}">
    <title>Sala de Espera</title>
    @vite(['resources/css/app.css', 'resources/js/kiosco.js'])
</head>
<body>
    <div id="app">
        <pantalla-kiosco-TV></pantalla-kiosco-TV>
    </div>
</body>
</html>