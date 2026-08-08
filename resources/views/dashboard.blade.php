@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Panel de administración Médico Online</h1>
@stop

@section('content')
    <p>Seguimiento de Pacientes y farmacia</p>
@stop

{{-- 👇 Usamos @push en lugar de @section para no romper el stack global de estilos --}}
@push('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@endpush

{{-- 👇 Igual con los scripts: @push agrega al stack sin sobreescribir --}}
@push('js')
    <script> console.log('Hi!'); </script>
@endpush