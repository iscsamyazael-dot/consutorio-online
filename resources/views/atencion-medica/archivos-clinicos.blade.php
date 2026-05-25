@extends('adminlte::page')

@section('title', 'Archivos Clínicos')

@section('content_header')

@section ('content')

<div class="content">
        <div class="content-header">
            <div class="container-fluid">
                <input type="hidden" name="route" value="{{ url('/') }}">

                <div id="app">
                    <atencion-medica-archivosclinicos></atencion-medica-archivosclinicos>
                </div>
            </div>
        </div>
</div>




    



   

</div>

@stop

@section ('js') 
    @vite ('resources/js/app.js')
@stop