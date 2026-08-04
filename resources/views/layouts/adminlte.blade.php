@extends('adminlte::page')

@section('css')
    @parent
    @vite(['resources/css/app.css'])
@stop

@section('js')
    @parent
    @vite(['resources/js/app.js'])
@stop 