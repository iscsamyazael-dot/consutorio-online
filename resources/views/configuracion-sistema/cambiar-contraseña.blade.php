@extends('adminlte::page')

@section('title', 'Cambiar Contraseña')

@section('content_header')
<input type="hidden" name="route" value="{{ url('/') }}">

@stop

@section('content')
<div id="app">
    <configuracion-sistema-panelcontrasena></configuracion-sistema-panelcontrasena>
</div>
</div>

@stop


@section('js')
@vite('resources/js/app.js')
@stop
