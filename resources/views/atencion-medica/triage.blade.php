
@extends('adminlte::page')

@section('title', 'TRIAGE')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">
    <meta name="base-url" content="{{ url('/') }}">
    <input type="hidden" name="route" value="{{ url('/') }}">
    
    
</div>
@stop
@section('content')
<div id="app">
    <atencion-medica></atencion-medica>
</div>
</div>

@stop

@section ('js') 
    @vite ('resources/js/app.js')
@stop