@extends('adminlte::page')

@section('title', 'Registrar Doctor')

@section('content')

<meta name="base-url" content="{{ url('/') }}">
<input type="hidden" name="route" value="{{ url('/') }}">

<div id="app">
    <registro-medicos></registro-medicos>
</div>

@stop

@section('js')
    @vite('resources/js/app.js')
@stop