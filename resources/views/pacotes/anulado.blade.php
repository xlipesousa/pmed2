@extends('adminlte::page')

@section('title', 'Pacote Anulado #' . $auditoria->pacote_id)

@section('content_header')
    <h1><i class="fas fa-ban text-danger"></i> Pacote Anulado #{{ $auditoria->pacote_id }}</h1>
@stop

@section('content')
<div class="container-fluid">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Início</a></li>
            <li class="breadcrumb-item"><a href="{{ route('configuracoes.anulacao') }}">Anulação</a></li>
            <li class="breadcrumb-item active">Pacote #{{ $auditoria->pacote_id }}</li>
        </ol>
    </nav>
    
    <!-- Alerta de Pacote Anulado -->
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="fas fa-ban"></i> Pacote Anulado</h4>
        <p><strong>Motivo:</strong> {{ $auditoria->motivo_anulacao }}</p>
        <p><strong>Data da Anulação:</strong> {{ $auditoria->data_anulacao->format('d/m/Y H:i:s') }}</p>
        <p><strong>Usuário:</strong> {{ $auditoria->usuarioAnulacao->name ?? 'Sistema' }}</p>
    </div>

    <div class="row">
        <!-- Informações Básicas -->
        <div class="col-md-3">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Informações Básicas</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>N° do Pacote</strong></td>
                            <td><span class="badge badge-danger">#{{ $auditoria->pacote_id }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>OCS/PSA</strong></td>
                            <td>{{ $auditoria->ocs_psa_nome }}</td>
                        </tr>
                        <tr>
                            <td><strong>Data da Entrada</strong></td>
                            <td>{{ $auditoria->data_entrada_original->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Número da Fatura</strong></td>
                            <td>{{ $auditoria->numero_fatura }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tipo</strong></td>
                            <td>{{ $auditoria->tipo_pacote_nome }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Valores Originais -->
        <div class="col-md-3">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Valores Originais</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Valor da Fatura</strong></td>
                            <td>R$ {{ number_format($auditoria->valor_fatura_original, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Glosa</strong></td>
                            <td>R$ {{ number_format($auditoria->valor_glosa_original, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Valor Pós Lisura</strong></td>
                            <td>R$ {{ number_format($auditoria->valor_pos_lisura_original ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Valor Pago</strong></td>
                            <td>R$ {{ number_format($auditoria->valor_pago_original, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Valor Pendente</strong></td>
                            <td>R$ {{ number_format($auditoria->valor_pendente_original, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Estado Original -->
        <div class="col-md-3">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Estado Original</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Tipo de Conta</strong></td>
                            <td>{{ $auditoria->tipo_conta_nome }}</td>
                        </tr>
                        <tr>
                            <td><strong>Estado Geral</strong></td>
                            <td><span class="badge badge-info">{{ $auditoria->estado_geral_no_momento }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Estado da Glosa</strong></td>
                            <td><span class="badge badge-warning">{{ $auditoria->estado_glosa_no_momento }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Localização</strong></td>
                            <td><span class="badge badge-secondary">{{ ucfirst($auditoria->localizacao_no_momento) }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Dados da Anulação -->
        <div class="col-md-3">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">Dados da Anulação</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Estado Atual</strong></td>
                            <td><span class="badge badge-danger">Anulado</span></td>
                        </tr>
                        <tr>
                            <td><strong>Localização Atual</strong></td>
                            <td><span class="badge badge-dark">anulado</span></td>
                        </tr>
                        <tr>
                            <td><strong>Data/Hora</strong></td>
                            <td>{{ $auditoria->data_anulacao->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Usuário</strong></td>
                            <td>{{ $auditoria->usuarioAnulacao->name ?? 'Sistema' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Motivo da Anulação -->
    <div class="row">
        <div class="col-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Motivo da Anulação</h3>
                </div>
                <div class="card-body">
                    <p class="text-justify">{{ $auditoria->motivo_anulacao }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <a href="{{ route('configuracoes.anulacao') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Voltar para Anulação
                    </a>
                    
                    <a href="{{ url('/pacotes/' . $auditoria->pacote_id) }}" class="btn btn-info btn-lg ml-3">
                        <i class="fas fa-eye"></i> Ver Pacote Atual (Zerado)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
@stop