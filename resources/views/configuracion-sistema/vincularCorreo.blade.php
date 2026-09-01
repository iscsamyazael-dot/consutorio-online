@extends('adminlte::page')

@section('title', 'Vinculacion Correo electronico')

@section('content_header')
<input type="hidden" name="route" value="{{ url('/') }}">
<meta name="base-url" content="{{ url('/') }}">
@stop
@section('content')
<div id="app">
    <vincular-correo></vincular-correo>
</div>
@stop
@section('js')
@vite('resources/js/app.js')
@stop