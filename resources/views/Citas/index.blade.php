@extends('adminlte::page')

@section('title', 'Agenda Médica')

@section('content_header')
<input type="hidden" name="route" value="{{ url('/') }}">
<meta name="base-url" content="{{url('/')}}">
    
@stop
@section('content')
<div id="app">
    <masteragenda></masteragenda>
</div>
@stop
@section('js')
@vite('resources/js/app.js')
@stop
    