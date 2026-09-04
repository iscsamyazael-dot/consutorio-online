@extends('adminlte::page')

@section('title', 'Consulta Inteligente')


@section('content')
<div class="container-fluid">

    <input type="hidden" name="route" value="{{ url('/') }}">
    <meta name="base-url" content="{{ url('/') }}">

    <div class="card shadow-sm border-0 mb-3" id="headerClinico">

        
    </div>

    <div id="app">
        <div class="row">
            <!-- COMPONENTE CONSULTA INTELIGENTE -->
            <consulta-inteligente
                paciente-id="{{ $paciente->id ?? '' }}">
            </consulta-inteligente>
        </div>
    </div>
</div>
@stop
@section('css')
<style>
.direct-chat-messages{

    background:#f4f6f9;

}



.timeline{

    position:relative;

}



.small-box h5{

    font-size:18px;

    font-weight:bold;

}



.card{

    border-radius:10px;

}


</style>


@stop





@section('js')


<script>

    window.pacienteId = "{{ $paciente->id ?? '' }}";

</script>


@vite('resources/js/app.js')


@stop