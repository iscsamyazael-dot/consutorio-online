@extends('adminlte::page')

@section('title', 'Programar cita')

@section('content_header')
    <h1>Programar cita</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-calendar-plus"></i> Datos de la cita
        </div>
        <div class="card-body">
            <form action="{{ route('agenda.store') }}" method="POST">
                @include('agenda._form')
            </form>
        </div>
    </div>
@stop
