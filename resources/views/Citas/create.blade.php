@extends('adminlte::page')

@section('title', 'Programar Cita')

@section('content_header')
<input type="hidden" name="route" value="{{ url('/') }}">
<meta name="base-url" content="{{url('/')}}">
@stop

@section('content')
<div id="app">
    <masterprocita
        store-url="{{ route('citas.store') }}"
        index-url="{{ route('citas.index') }}"
        csrf-token="{{ csrf_token() }}"
        :pacientes="{{ json_encode($pacientes) }}"
        :medicos="{{ json_encode($medicos) }}"
        :especialidades="{{ json_encode($especialidades) }}"
    ></masterprocita>
</div>
@stop
@section('js')
@vite('resources/js/app.js')
@stop