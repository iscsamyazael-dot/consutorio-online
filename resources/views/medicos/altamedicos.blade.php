@extends('adminlte::page')

@section('title', 'Gestión de Doctores')

@section('content_header')

@section('content')
  <meta name="base-url" content="{{ url('/') }}">
  <input type="hidden" name="route" value="{{ url('/') }}">

    <div id="app">
    <alta-medicos></alta-medicos>
</div>
</div>

@stop


@section ('js') 
    @vite ('resources/js/app.js')
@stop