@extends('adminlte::page')

@section('title', 'Nuevo Usuario')

@section('content_header')
@section('content')
<meta name="base-url" content="{{ url('/') }}">
<input type="hidden" name="route" value="{{ url('/') }}">
<div id="app">
    <registro-usuario></registro-usuario>
</div>
</div>


@stop

@section ('js') 
    @vite ('resources/js/app.js')
@stop