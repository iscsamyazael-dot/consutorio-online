@extends('adminlte::page')
@section('title', 'Registrar Doctor')
@section('content')

<meta name="base-url" content="{{ url('/') }}">
<input type="hidden" name="route" value="{{ url('/') }}">

<div id="app">
    <registro-medicos></registro-medicos>
</div>
<div class="position-fixed p-3" style="z-index: 9999; right: 20px; top: 20px; min-width: 300px;">
    
    @if(session('success'))
        <div id="toast-success" class="toast show border-0 shadow-lg text-white" role="alert" aria-live="assertive" aria-atomic="true" data-delay="4000" style="background: linear-gradient(135deg, #00bfa5 0%, #00897b 100%); border-radius: 12px; cubic-bezier(0.16, 1, 0.3, 1); animation: slideInMedico 0.4s ease-out;">
            <div class="d-flex align-items-center px-3 py-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="background-color: rgba(255, 255, 255, 0.2); width: 30px; height: 30px;">
                    <i class="fas fa-check-circle text-white"></i>
                </div>
                <div class="toast-body p-0 font-weight-bold flex-grow-1">
                    {{ session('success') }}
                </div>
                <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close" style="outline: none; opacity: 0.8;">
                    <span aria-hidden="true" class="text-white">&times;</span>
                </button>
            </div>
        </div>
    @endif

@stop

@section('js')
    @vite('resources/js/app.js')
@stop