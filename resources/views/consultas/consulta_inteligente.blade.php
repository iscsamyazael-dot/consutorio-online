@extends('adminlte::page')

@section('title', 'Consulta Inteligente')


@section('content')
<div class="container-fluid">

    <input type="hidden" name="route" value="{{ url('/') }}">
    <meta name="base-url" content="{{ url('/') }}">

    {{--
        HEADER CLÍNICO.
        Este header vive dentro de la sección content, NO en
        content_header. content_header es un contenedor muy pequeño
        (solo el alto del propio header) y el sticky necesita un
        contenedor alto para poder seguirte durante todo el scroll.
        Si se pone en content_header, el sticky se rompe apenas bajas
        un poco (se despega y se ve encimado/raro).
        El "top" usa la variable navbar-height (medida real del
        navbar por JS, ver el bloque de JS al final del archivo) para
        pegarse justo debajo del navbar fijo de AdminLTE, sin quedar
        tapado detrás de él.
    --}}
    <div class="card shadow-sm border-0 mb-3 sticky-top" id="headerClinico"
         style="top: var(--navbar-height, 57px); z-index: 1020;">

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


/* Header clínico fijo: sombra un poco más marcada cuando queda
   pegado arriba, para distinguirlo visualmente del contenido que
   pasa debajo. background-color evita que se transparente el
   contenido de abajo mientras está pegado. */
#headerClinico.sticky-top {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    background-color: #fff;
}


</style>


@stop





@section('js')


<script>

    window.pacienteId = "{{ $paciente->id ?? '' }}";

    // Mide el navbar fijo de AdminLTE (el de la lupa, campana,
    // "Administrador General") y guarda su altura real en la
    // variable CSS navbar-height. headerClinico usa esa variable
    // como su "top" en vez de 0, para pegarse justo debajo del
    // navbar en vez de quedar tapado detrás de él al hacer scroll.
    function ajustarNavbarHeight() {
        var navbar = document.querySelector('.main-header.navbar') || document.querySelector('nav.main-header');
        var altura = navbar ? navbar.offsetHeight : 57;
        document.documentElement.style.setProperty('--navbar-height', altura + 'px');
    }
    ajustarNavbarHeight();
    // Se recalcula también en window load (no solo al ejecutar este
    // script), por si el navbar cambia de alto una vez que cargan
    // fuentes/íconos.
    window.addEventListener('load', ajustarNavbarHeight);
    window.addEventListener('resize', ajustarNavbarHeight);

</script>


@vite('resources/js/app.js')


@stop