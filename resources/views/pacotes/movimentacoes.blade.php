@extends('adminlte::page')

@section('title', 'Histórico de Movimentações')

@section('content_header')
    <h1>
        Histórico de Movimentações - Pacote #{{ $pacote->id ?? request('id') }}
        <a href="{{ route('pacotes.show', ['id' => $pacote->id ?? request('id')]) }}" class="btn btn-sm btn-secondary float-right">
            <i class="fas fa-arrow-left"></i> Voltar para Detalhes
        </a>
    </h1>
@stop

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Início</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pacotes.index') }}">Pacotes</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pacotes.show', ['id' => $pacote->id ?? request('id')]) }}">Pacote #{{ $pacote->id ?? request('id') }}</a></li>
        <li class="breadcrumb-item active">Movimentações</li>
    </ol>

    <!-- Histórico de movimentações -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i>
                Histórico de Movimentações
            </h3>
        </div>
        <div class="card-body">
            <div class="timeline">
                @if(isset($movimentacoes) && $movimentacoes->count() > 0)
                    @foreach($movimentacoes as $movimentacao)
                        <div>
                            @php
                                // Definir o ícone com base no tipo de ação
                                if($movimentacao->acao === 'Criar novo Pacote') {
                                    $iconeClass = 'fas fa-plus bg-success';
                                } elseif($movimentacao->acao === 'Movimentação') {
                                    $iconeClass = 'fas fa-arrow-right bg-info';
                                } elseif($movimentacao->acao === 'Implantar Pagamento') {
                                    $iconeClass = 'fas fa-money-bill-wave bg-success'; // Ícone de dinheiro com cor amarela
                                } elseif($movimentacao->acao === 'Aguardando Limite de Crédito') {
                                    $iconeClass = 'fas fa-exclamation-triangle'; // Triângulo com fundo laranja personalizado
                                    // Aplicar estilo personalizado para este ícone
                                    $iconeStyle = 'background-color: #ff8c00; color: white;';
                                } elseif($movimentacao->acao === 'Edição') {
                                    $iconeClass = 'fas fa-edit bg-primary';
                                } elseif($movimentacao->acao === 'Notificação de Existência de Glosa' || 
                                          $movimentacao->acao === 'Retirada de Ofício de Glosa' || 
                                          $movimentacao->acao === 'Recurso não recebido') {
                                    $iconeClass = 'fas fa-balance-scale bg-primary'; // Ícone de balança com cor azul primary
                                } elseif($movimentacao->acao === 'Arquivo' || 
                                          $movimentacao->acao === 'Atualização de Localização Física') {
                                    $iconeClass = 'fas fa-archive bg-primary'; // Ícone de arquivo com cor azul primary
                                } else {
                                    $iconeClass = 'fas fa-cog bg-secondary';
                                }
                            @endphp
                            
                            {{-- Se houver estilo personalizado, aplique-o --}}
                            @if(isset($iconeStyle))
                                <i class="{{ $iconeClass }}" style="{{ $iconeStyle }}"></i>
                            @else
                                <i class="{{ $iconeClass }}"></i>
                            @endif
                            
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $movimentacao->created_at->timezone('UTC')->format('d/m/Y H:i:s') }}</span>
                                <h3 class="timeline-header"><b>{{ $movimentacao->acao }}</b></h3>
                                <div class="timeline-body">
                                    {{ $movimentacao->mensagem }}
                                    
                                    @if($movimentacao->observacao)
                                        <div class="mt-2">
                                            <strong>Observação:</strong> {{ $movimentacao->observacao }}
                                        </div>
                                    @endif
                                </div>
                                <div class="timeline-footer">
                                    <span class="badge bg-primary">Estado Geral: {{ $movimentacao->estado_geral }}</span>
                                    <span class="badge bg-{{ $movimentacao->estado_glosa == 'Não identificada' ? 'secondary' : 'danger' }}">
                                        Estado da Glosa: {{ $movimentacao->estado_glosa }}
                                    </span>
                                    <span class="badge bg-info">
                                        Localização pós ação: {{ ucfirst($movimentacao->localizacao_pos_acao) }}
                                    </span>
                                    <span class="badge bg-secondary">Usuário: {{ optional($movimentacao->usuario)->name ?? 'Sistema' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nenhuma movimentação encontrada para este pacote.
                    </div>
                @endif
                
                <div>
                    <i class="fas fa-clock bg-gray"></i>
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
            margin-right: 10px;
            position: relative;
        }
        
        .timeline > div > i {
            width: 30px;
            height: 30px;
            font-size: 15px;
            line-height: 30px;
            position: absolute;
            border-radius: 50%;
            text-align: center;
            left: 18px;
            color: #fff;
        }
        
        .timeline-item {
            -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            border-radius: 3px;
            margin-left: 60px;
            margin-right: 15px;
            padding: 0;
            background: #fff;
            color: #444;
        }
        
        .timeline-header {
            padding: 10px;
            font-size: 16px;
            line-height: 1.1;
            margin: 0;
            border-bottom: 1px solid #f4f4f4;
        }
        
        .timeline-body {
            padding: 10px;
        }
        
        .timeline-footer {
            padding: 10px;
            background-color: #f4f4f4;
        }
        
        .time {
            color: #999;
            float: right;
            padding: 10px;
            font-size: 12px;
        }
    </style>
@stop