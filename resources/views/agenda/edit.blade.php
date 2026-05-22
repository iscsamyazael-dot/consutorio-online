@extends('adminlte::page')

@section('title', 'Editar cita')

@section('content_header')
    <h1>Editar cita</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-warning">
            <i class="fas fa-calendar-alt"></i> Actualizar cita
        </div>
        <div class="card-body">
            <form action="{{ route('agenda.update', $cita) }}" method="POST">
                @method('PUT')
                @include('agenda._form')
            </form>
        </div>
    </div>
@stop
