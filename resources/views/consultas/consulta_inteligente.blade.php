@extends('adminlte::page')

@section('title', 'Consulta Inteligente')


@section('content')
<div class="container-fluid">

    <input type="hidden" name="route" value="{{ url('/') }}">
    <meta name="base-url" content="{{ url('/') }}">

    <div class="card shadow-sm border-0 mb-3" id="headerClinico">

        <div class="card-body py-3">

            <div class="row align-items-center">


                <!-- PACIENTE -->
                <div class="col-lg-4 d-flex align-items-center">


                    <img src="https://i.pravatar.cc/80"
                         class="img-circle elevation-2 mr-3"
                         width="60">

                    <div>
                       <h5 class="mb-0 font-weight-bold" id="nombrePaciente">
                            {{ $paciente->nombre ?? 'Sin paciente seleccionado' }}
                        </h5>
                        <small class="text-muted" id="datosPaciente">
                            --
                        </small>
                        <div class="mt-1">
                            <span class="badge badge-success">
                                Consulta activa
                            </span>
                            <span class="badge badge-warning">
                                IA activa
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ESTADO CONSULTA -->
                <div class="col-lg-4 text-center">
                    <div class="row">
                        <div class="col-4">
                            <h5 class="font-weight-bold text-primary mb-0">
                                00m
                            </h5>
                            <small>
                                Consulta
                            </small>
                        </div>
                        <div class="col-4">
                            <h5 class="font-weight-bold text-success mb-0">
                                IA ON
                            </h5>
                            <small>
                                Asistente
                            </small>
                        </div>
                        <div class="col-4">
                            <h5 class="font-weight-bold text-info mb-0">
                                LIVE
                            </h5>
                            <small>
                                Transcripción
                            </small>
                        </div>
                    </div>
                </div>
                <!-- ACCIONES -->
                <div class="col-lg-4 text-right">
                    <button class="btn btn-primary btn-sm">
                        <i class="fas fa-microphone"></i>
                        Escuchar
                    </button>
                    <button class="btn btn-success btn-sm">
                        <i class="fab fa-whatsapp"></i>
                        WhatsApp
                    </button>
                    <button class="btn btn-danger btn-sm">
                        <i class="fas fa-stop-circle"></i>
                        Finalizar
                    </button>
                </div>
            </div>
        </div>
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