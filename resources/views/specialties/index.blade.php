@extends('adminlte::page')

@section('title', 'Especialidades Médicas')

@section('content_header')
 <meta name="base-url" content="{{ url('/') }}">
 <input type="hidden" name="route" value="{{url('/') }}">
 @stop
 @section('content')
 <div id="app">
    <Especialidades></Especialidades>
 </div>
 </div>

 @stop

 @section('js')
 @vite('resources/js/app.js')
 @stop
 