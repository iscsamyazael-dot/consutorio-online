
@extends('adminlte::page')

@section('title', 'Recetas Médicas')
@section('content_header')
@section('content')
   <meta name="base-url" content="{{ url('/') }}">
 <input type="hidden" name="route" value="{{ url('/') }}">

<div id="app">
    <recetass-Historial></recetass-Historial>
</div>
</div>

@stop

@section ('js') 
    @vite ('resources/js/app.js')
@stop