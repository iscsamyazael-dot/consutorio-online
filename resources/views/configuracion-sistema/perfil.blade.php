@extends('adminlte::page')

@section('title', 'Perfil')

@section('content_header')
<meta name="base-url" content="{{ url('/') }}">
<input type="hidden" name="route" value="{{ url('/') }}">


@stop

@section('content')
<div id="app">
    <configuracion-sistema></configuracion-sistema>
</div>
</div>

@stop


@section('js')
@vite('resources/js/app.js')
@stop
