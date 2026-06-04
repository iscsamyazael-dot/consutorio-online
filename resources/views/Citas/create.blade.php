@extends('adminlte::page')

@section('title', 'Programar Cita')

@section('content_header')
<input type="hidden" name="route" value="{{ url('/') }}">
    
@stop
@section('content')
<div id="app">
    <masterprocita></masterprocita>
</div>
@stop
@section('js')
@vite('resources/js/app.js')
@stop