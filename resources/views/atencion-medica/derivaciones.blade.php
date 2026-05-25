@extends('adminlte::page')

@section('title', 'Derivaciones')

@section('content_header')

@section('content')
  <input type="hidden" name="route" value="{{ url('/') }}">

    <div id="app">
    <atencion-medica-derivaciones></atencion-medica-derivaciones>
</div>
</div>
@stop




@section ('js') 
    @vite ('resources/js/app.js')
@stop