@extends('adminlte::page')

@section('title', 'Historial de Consultas - Sistema de Triage')
@section('content_header')


@section('content')
<meta name="base-url" content="{{ url('/') }}">
<input type="hidden" name="route" value="{{ url('/') }}">

<div id="app">
    <historial-consulta></historial-consulta>
</div>
@stop

@section('js')
    @vite('resources/js/app.js')
@stop

@section('css')

