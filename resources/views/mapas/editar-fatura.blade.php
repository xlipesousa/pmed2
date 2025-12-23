@extends('adminlte::page')

@section('title', 'Editar Fatura no Mapa')

@section('content_header')
    <h1>Editar Fatura no Mapa</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dados da Fatura</h3>
            <div class="float-right">
                <a href="{{ route('mapas.show', $mapa->id) }}" class="btn btn-sm btn-secondary">Voltar para o Mapa</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Número do Mapa:</strong> {{ $mapa->numero_mapa }}</p>
                    <p><strong>Número da Fatura:</strong> {{ $pacote->numero_fatura }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Valor Total da Fatura:</strong> R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</p>
                    <p><strong>Valor Implantado:</strong> R$ {{ number_format($pacote->valor_pendente + $mapaPacote->valor_parcial, 2, ',', '.') }}</p>
                </div>
            </div>
            
            <form action="{{ route('mapas.atualizar-fatura', [$mapa->id, $pacote->id]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="valor_parcial">Valor Empenhado</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="number" step="0.01" name="valor_parcial" id="valor_parcial" class="form-control @error('valor_parcial') is-invalid @enderror" value="{{ old('valor_parcial') ?? $mapaPacote->valor_parcial }}" required>
                                @error('valor_parcial')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="empenho">Nº do Empenho</label>
                            <input type="text" name="empenho" id="empenho" class="form-control @error('empenho') is-invalid @enderror" value="{{ old('empenho') ?? $mapaPacote->empenho }}">
                            @error('empenho')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="data_empenho">Data do Empenho</label>
                            <input type="date" name="data_empenho" id="data_empenho" class="form-control @error('data_empenho') is-invalid @enderror" value="{{ old('data_empenho') ?? ($mapaPacote->data_empenho ? $mapaPacote->data_empenho->format('Y-m-d') : '') }}">
                            @error('data_empenho')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nota_fiscal">Nota Fiscal</label>
                            <input type="text" name="nota_fiscal" id="nota_fiscal" class="form-control @error('nota_fiscal') is-invalid @enderror" value="{{ old('nota_fiscal') ?? $mapaPacote->nota_fiscal }}">
                            @error('nota_fiscal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="data_nota_fiscal">Data da Nota Fiscal</label>
                            <input type="date" name="data_nota_fiscal" id="data_nota_fiscal" class="form-control @error('data_nota_fiscal') is-invalid @enderror" value="{{ old('data_nota_fiscal') ?? ($mapaPacote->data_nota_fiscal ? $mapaPacote->data_nota_fiscal->format('Y-m-d') : '') }}">
                            @error('data_nota_fiscal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Atualizar</button>
                    <a href="{{ route('mapas.show', $mapa->id) }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@stop