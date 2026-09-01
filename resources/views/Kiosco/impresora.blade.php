@extends('adminlte::page')

@section('title', 'Agregar Dispositivo')

@section('content_header')
<input type="hidden" name="route" value="{{ url('/') }}">
<meta name="base-url" content="{{ url('/') }}">
@stop
@section('content')
<div id="app">
    <configuracion-impresora></configuracion-impresora>
</div>
@stop
@section('js')
@vite('resources/js/app.js')
@stop