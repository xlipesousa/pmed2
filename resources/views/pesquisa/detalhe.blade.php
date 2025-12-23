@extends('adminlte::page')

@section('title', 'Detalhes do Pacote')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-box"></i> Pacote #{{ $pacote->id }}</h1>
        <div>
            <a href="{{ route('pacotes.show', $pacote->id) }}" class="btn btn-info">
                <i class="fas fa-edit"></i> Visualizar no Sistema
            </a>
            <a href="{{ route('pesquisa.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar para Pesquisa
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Informações Básicas -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informações do Pacote</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th style="width: 200px">Número do Pacote</th>
                            <td>{{ $pacote->id }}</td>
                        </tr>
                        <tr>
                            <th>Número da Fatura</th>
                            <td>{{ $pacote->numero_fatura }}</td>
                        </tr>
                        <tr>
                            <th>OCS/PSA</th>
                            <td>{{ $pacote->ocsPsa->nome ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Tipo de Pacote</th>
                            <td>{{ $pacote->tipoPacote->nome ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Tipo de Conta</th>
                            <td>{{ $pacote->tipoConta->nome ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Data de Entrada</th>
                            <td>{{ $pacote->data_entrada ? $pacote->data_entrada->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Localização Atual</th>
                            <td>
                                <span class="badge badge-{{ 
                                    $pacote->localizacao_atual == 'protocolo' ? 'primary' :
                                    ($pacote->localizacao_atual == 'lisura' ? 'success' :
                                    ($pacote->localizacao_atual == 'sire' ? 'info' :
                                    ($pacote->localizacao_atual == 'glosa' ? 'warning' :
                                    ($pacote->localizacao_atual == 'arquivo' ? 'secondary' : 'dark'))))
                                }}">
                                    {{ ucfirst($pacote->localizacao_atual) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Localização Anterior</th>
                            <td>{{ $pacote->localizacao_anterior }}</td>
                        </tr>
                        <tr>
                            <th>Localização Física</th>
                            <td>{{ $pacote->localizacao_fisica ?? 'Não informada' }}</td>
                        </tr>
                        <tr>
                            <th>Última Ação</th>
                            <td>{{ $pacote->ultima_acao }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Valores Financeiros -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title"><i class="fas fa-dollar-sign"></i> Informações Financeiras</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Valor da Fatura</span>
                                <span class="info-box-number">R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Valor da Glosa</span>
                                <span class="info-box-number">R$ {{ number_format($pacote->valor_glosa, 2, ',', '.') }}</span>
                                @if($pacote->valor_fatura > 0)
                                <span class="info-box-text">
                                    {{ number_format(($pacote->valor_glosa / $pacote->valor_fatura) * 100, 1) }}% do valor total
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Valor Pós Lisura</span>
                                <span class="info-box-number">R$ {{ number_format($pacote->valor_pos_lisura, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Valor Pago</span>
                                <span class="info-box-number">R$ {{ number_format($pacote->valor_pago, 2, ',', '.') }}</span>
                                @if($pacote->valor_pos_lisura > 0)
                                <span class="info-box-text">
                                    {{ number_format(($pacote->valor_pago / $pacote->valor_pos_lisura) * 100, 1) }}% do valor pós lisura
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Valor Pendente</span>
                                <span class="info-box-number">R$ {{ number_format($pacote->valor_pendente, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($pacote->temGlosa())
                    <div class="col-md-6">
                        <div class="info-box bg-purple">
                            <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Valor Deferido</span>
                                <span class="info-box-number">R$ {{ number_format($pacote->valor_deferido, 2, ',', '.') }}</span>
                                @if($pacote->valor_glosa > 0)
                                <span class="info-box-text">
                                    {{ number_format(($pacote->valor_deferido / $pacote->valor_glosa) * 100, 1) }}% do valor glosado
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                
                @if($pacote->temGlosa())
                <div class="mt-3">
                    <h5><i class="fas fa-info-circle"></i> Detalhes da Glosa</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 200px">Estado da Glosa</th>
                            <td>
                                <span class="badge badge-{{ 
                                    $pacote->estado_glosa == 'Glosa não identificada' ? 'light' :
                                    ($pacote->estado_glosa == 'Glosa identificada' ? 'warning' :
                                    ($pacote->estado_glosa == 'Existência de Glosa Notificada' ? 'warning' :
                                    ($pacote->estado_glosa == 'Recurso não recebido' ? 'danger' :
                                    ($pacote->estado_glosa == 'Recurso recebido' ? 'info' :
                                    ($pacote->estado_glosa == 'Recurso indeferido' ? 'danger' :
                                    ($pacote->estado_glosa == 'Recurso deferido' ? 'success' : 'secondary'))))))
                                }}">
                                    {{ $pacote->estado_glosa }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Motivo da Glosa</th>
                            <td>{{ $pacote->motivoGlosa->nome ?? 'Não informado' }}</td>
                        </tr>
                        <tr>
                            <th>Descrição da Glosa</th>
                            <td>{{ $pacote->descricao_glosa ?? 'Não informada' }}</td>
                        </tr>
                        <tr>
                            <th>Valor Recursado</th>
                            <td>R$ {{ number_format($pacote->valor_recursado, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Histórico de Movimentações -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-history"></i> Histórico de Movimentações</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @if($pacote->movimentacoes->count() > 0)
                        @foreach($pacote->movimentacoes as $movimentacao)
                            <div class="time-label">
                                <span class="bg-info">{{ $movimentacao->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div>
                                @php
                                    // Determinar o ícone e cor baseado no tipo de ação
                                    $iconeClass = 'fas fa-cog bg-secondary';
                                    
                                    if (strpos($movimentacao->acao, 'Movimentação') !== false) {
                                        $iconeClass = 'fas fa-arrow-right bg-primary';
                                    } elseif (strpos($movimentacao->acao, 'Criar') !== false) {
                                        $iconeClass = 'fas fa-plus bg-success';
                                    } elseif (strpos($movimentacao->acao, 'Pagamento') !== false) {
                                        $iconeClass = 'fas fa-money-bill-wave bg-success';
                                    } elseif (strpos($movimentacao->acao, 'Glosa') !== false) {
                                        $iconeClass = 'fas fa-exclamation-triangle bg-warning';
                                    } elseif (strpos($movimentacao->acao, 'Arquiv') !== false) {
                                        $iconeClass = 'fas fa-archive bg-secondary';
                                    } elseif (strpos($movimentacao->acao, 'Recurso') !== false) {
                                        $iconeClass = 'fas fa-balance-scale bg-info';
                                    } elseif (strpos($movimentacao->acao, 'Edição') !== false) {
                                        $iconeClass = 'fas fa-edit bg-primary';
                                    }
                                @endphp
                                
                                <i class="{{ $iconeClass }}"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $movimentacao->created_at->format('H:i:s') }}</span>
                                    <h3 class="timeline-header"><b>{{ $movimentacao->acao }}</b></h3>
                                    <div class="timeline-body">
                                        {{ $movimentacao->mensagem }}
                                        
                                        @if($movimentacao->observacao)
                                            <hr>
                                            <p><strong>Observações:</strong> {{ $movimentacao->observacao }}</p>
                                        @endif
                                    </div>
                                    <div class="timeline-footer">
                                        <span class="badge badge-primary">Localização: {{ $movimentacao->localizacao_pos_acao }}</span>
                                        <span class="badge badge-info">Estado Geral: {{ $movimentacao->estado_geral }}</span>
                                        <span class="badge badge-warning">Estado Glosa: {{ $movimentacao->estado_glosa }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhuma movimentação registrada para este pacote.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Observações -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary">
                <h3 class="card-title"><i class="fas fa-sticky-note"></i> Observações</h3>
            </div>
            <div class="card-body">
                @if($pacote->observacoes)
                    <p>{{ $pacote->observacoes }}</p>
                @else
                    <p class="text-muted">Nenhuma observação registrada.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .timeline {
        margin: 0;
        padding: 0;
        position: relative;
        list-style: none;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #ddd;
        left: 31px;
        margin: 0;
        border-radius: 2px;
    }
    
    .timeline > div {
        margin-bottom: 15px;
        position: relative;
    }
    
    .time-label {
        margin-bottom: 10px;
    }
    
    .time-label > span {
        display: inline-block;
        padding: 5px 10px;
        font-weight: 600;
        border-radius: 4px;
        color: white;
    }
    
    .timeline > div > i {
        width: 30px;
        height: 30px;
        font-size: 15px;
        line-height: 30px;
        position: absolute;
        color: #fff;
        border-radius: 50%;
        text-align: center;
        left: 18px;
        top: 0;
    }
    
    .timeline-item {
        -webkit-box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        border-radius: 3px;
        margin-left: 60px;
        margin-right: 15px;
        padding: 0;
        position: relative;
        background-color: #fff;
        margin-top: 0;
    }
    
    .timeline-item > .time {
        color: #999;
        float: right;
        padding: 10px;
        font-size: 12px;
    }
    
    .timeline-item > .timeline-header {
        margin: 0;
        color: #555;
        border-bottom: 1px solid #f4f4f4;
        padding: 10px;
        font-size: 16px;
        line-height: 1.1;
    }
    
    .timeline-item > .timeline-body {
        padding: 10px;
    }
    
    .timeline-item > .timeline-footer {
        padding: 10px;
    }
    
    .timeline > div > i.bg-blue {
        background-color: #0073b7;
    }
    
    .timeline > div > i.bg-purple {
        background-color: #6f42c1;
    }
    
    .bg-purple {
        background-color: #6f42c1 !important;
        color: white !important;
    }
    
    .info-box {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 0.25rem;
    }
    
    .info-box-content {
        padding: 10px 10px 5px 0;
    }
    
    .info-box-text {
        font-weight: bold;
    }
</style>
@stop

@section('js')
<script>
    $(function() {
    });
</script>
@stop