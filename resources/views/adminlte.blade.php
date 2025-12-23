@extends('adminlte::page')

@section('title', 'Dashboard PMED 2.0')

@section('content_header')
    <h1>Dashboard PMED 2.0</h1>
@stop

@section('content')
    <p>Bem-vindo ao sistema PMED 2.0 usando AdminLTE!</p>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Exemplo de Card</h3>
        </div>
        <div class="card-body">
            O template AdminLTE foi instalado com sucesso!
        </div>
    </div>
@stop

@section('css')
    {{-- Adicionar estilos CSS personalizados aqui --}}
@stop

@section('js')
    <script> console.log('AdminLTE funcionando!'); </script>
@stop