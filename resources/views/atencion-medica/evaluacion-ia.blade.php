@extends('adminlte::page')

@section('title', 'Evaluación IA')

@section('content_header')
@section('content')
<input type="hidden" name="route" value="{{ url('/') }}">
<div id="app">
    <atencion-medica-evaluacionia></atencion-medica-evaluacionia>
</div>
</div>


@stop

@section ('js') 
    @vite ('resources/js/app.js')
@stop