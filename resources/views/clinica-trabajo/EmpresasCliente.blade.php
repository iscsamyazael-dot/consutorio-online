@extends('adminlte::page')
@section('title', 'Empresas cliente')
@section('content_header')
<h1>Catálogo de empresas cliente</h1>
@endsection
@section('content')
<div id="app">
    <master-empresas-cliente></master-empresas-cliente>
</div>
@endsection
@push('js')
@vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush