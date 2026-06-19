@extends('adminlte::page')

@section('title', 'Consultas Médicas')

@section('content_header')

   @stop
@section('content')
        <meta name="base-url" content="{{ url('/') }}">
        <input type="hidden" name="route" value="{{ url('/') }}">

        <div id="app">
            <centroconsultas></centroconsultas>
        </div>

@stop

@section('js')
    @vite('resources/js/app.js')
@stop