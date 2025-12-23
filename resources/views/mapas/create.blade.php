@extends('adminlte::page')

@section('title', 'Novo Mapa de Pagamento')

@section('content_header')
    <h1>Novo Mapa de Pagamento</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('mapas.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="numero_mapa">Número do Mapa</label>
                    <input type="text" name="numero_mapa" id="numero_mapa" class="form-control @error('numero_mapa') is-invalid @enderror" value="{{ old('numero_mapa') }}" required>
                    @error('numero_mapa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="data_criacao">Data de Liberação</label>
                    <input type="date" name="data_criacao" id="data_criacao" class="form-control @error('data_criacao') is-invalid @enderror" value="{{ old('data_criacao') ?? date('Y-m-d') }}" required>
                    @error('data_criacao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Criar Mapa</button>
                    <a href="{{ route('mapas.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@stop