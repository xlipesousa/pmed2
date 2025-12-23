@extends('adminlte::page')

@section('title', 'Prazos e Notificações')

@section('content_header')
    <h1>
        <i class="fas fa-clock"></i> Monitoramento de Prazos - Pacote #{{ $pacote->id }}
        <a href="{{ route('pacotes.show', $pacote->id) }}" class="btn btn-sm btn-secondary float-right">
            <i class="fas fa-arrow-left"></i> Voltar para Detalhes
        </a>
    </h1>
@stop

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Início</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pacotes.index') }}">Pacotes</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pacotes.show', $pacote->id) }}">Pacote #{{ $pacote->id }}</a></li>
        <li class="breadcrumb-item active">Prazos e Notificações</li>
    </ol>

    <!-- Status Overview -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Status Geral do Processo</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-file-invoice"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Fatura</span>
                                    <span class="info-box-number">{{ $pacote->numero_fatura }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Valor Original</span>
                                    <span class="info-box-number">R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Valor Glosado</span>
                                    <span class="info-box-number">R$ {{ number_format($pacote->valor_glosa, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon {{ $pacote->localizacao_atual == 'glosa' ? 'bg-warning' : 'bg-info' }}">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Localização Atual</span>
                                    <span class="info-box-number">{{ ucfirst($pacote->localizacao_atual) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Painel de Status de Glosa -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Status da Glosa</h3>
                </div>
                <div class="card-body">
                    <!-- Mostrar Status da Glosa com Ícone Visual -->
                    <div class="status-indicator text-center mb-4">
                        @php
                            $statusClass = '';
                            $statusIcon = '';
                            $statusText = '';
                            
                            if ($pacote->valor_glosa == 0) {
                                $statusClass = 'success';
                                $statusIcon = 'check-circle';
                                $statusText = 'Glosa não identificada';
                            } elseif (!$pacote->data_notificacao_glosa) {
                                $statusClass = 'warning';
                                $statusIcon = 'exclamation-circle';
                                $statusText = 'Glosa identificada - Pendente de Notificação';
                            } elseif (!$pacote->data_retirada_oficio) {
                                $statusClass = 'info';
                                $statusIcon = 'bell';
                                $statusText = 'Existência de Glosa Notificada';
                            } elseif (!$pacote->data_recebimento_recurso && $pacote->estado_glosa == 'Aguardando Recurso de Glosa') {
                                $statusClass = 'primary';
                                $statusIcon = 'hourglass-half';
                                $statusText = 'Aguardando Recurso de Glosa';
                            } elseif ($pacote->data_recebimento_recurso && !isset($pacote->valor_deferido)) {
                                $statusClass = 'info';
                                $statusIcon = 'search';
                                $statusText = 'Recurso em Análise';
                            } elseif (isset($pacote->valor_deferido)) {
                                $statusClass = 'success';
                                $statusIcon = 'check-double';
                                $statusText = 'Recurso Processado';
                            } else {
                                $statusClass = 'secondary';
                                $statusIcon = 'question-circle';
                                $statusText = 'Status Indefinido';
                            }
                        @endphp

                        <div class="status-icon text-{{ $statusClass }}">
                            <i class="fas fa-{{ $statusIcon }} fa-5x"></i>
                        </div>
                        <div class="status-text mt-2">
                            <h4>{{ $statusText }}</h4>
                            <p class="text-muted">{{ $pacote->estado_glosa }}</p>
                        </div>
                    </div>

                    <!-- Barra de Progresso do Fluxo de Glosa -->
                    <div class="progress-wrapper mt-4">
                        <p class="mb-1">Progresso do Fluxo de Glosa</p>
                        @php
                            $progress = 0;
                            if ($pacote->valor_glosa > 0) $progress += 20;
                            if ($pacote->data_notificacao_glosa) $progress += 20;
                            if ($pacote->data_retirada_oficio) $progress += 20;
                            if ($pacote->data_recebimento_recurso) $progress += 20;
                            if (isset($pacote->valor_deferido)) $progress += 20;
                        @endphp
                        
                        <div class="progress">
                            <div class="progress-bar bg-gradient-success progress-bar-striped" 
                                role="progressbar" 
                                style="width: {{ $progress }}%" 
                                aria-valuenow="{{ $progress }}" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                {{ $progress }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel de Prazos e Countdown -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-gradient-warning">
                    <h3 class="card-title"><i class="fas fa-stopwatch"></i> Prazos Críticos</h3>
                </div>
                <div class="card-body">
                    @if ($pacote->data_notificacao_glosa && !$pacote->data_retirada_oficio)
                        <!-- Prazo para Retirada de Ofício -->
                        <div class="countdown-box text-center p-3 mb-4 {{ $pacote->prazoRetiradaExcedido() ? 'bg-danger' : ($pacote->diasRetiradaRestantes() <= 3 ? 'bg-warning' : 'bg-info') }}">
                            <h5 class="text-white">Prazo para Retirada do Ofício</h5>
                            
                            @if ($pacote->prazoRetiradaExcedido())
                                <div class="countdown-timer">
                                    <i class="fas fa-exclamation-triangle fa-3x text-white mb-2"></i>
                                    <h3 class="text-white">PRAZO EXPIRADO</h3>
                                    <p class="text-white">
                                        Excedido em {{ intval(abs($pacote->diasRetiradaRestantes())) }} dias
                                    </p>
                                </div>
                            @else
                                <div class="countdown-timer">
                                    <i class="fas fa-hourglass-half fa-3x text-white mb-2"></i>
                                    <h2 class="text-white">{{ intval($pacote->diasRetiradaRestantes()) }}</h2>
                                    <p class="text-white">
                                        Dias Restantes
                                    </p>
                                </div>
                            @endif
                        </div>
                    @elseif ($pacote->data_retirada_oficio && !$pacote->data_recebimento_recurso)
                        <!-- Prazo para Recurso de Glosa -->
                        @php
                            // Verificar se há data limite para recurso (geralmente 30 dias após a retirada)
                            $dataLimiteRecurso = $pacote->data_retirada_oficio ? (clone $pacote->data_retirada_oficio)->addDays(30) : null;
                            $diasRestantesRecurso = $dataLimiteRecurso ? now()->diffInDays($dataLimiteRecurso, false) : null;
                            $recursoExpirado = $dataLimiteRecurso && now()->gt($dataLimiteRecurso);
                        @endphp
                        
                        <div class="countdown-box text-center p-3 mb-4 {{ $recursoExpirado ? 'bg-danger' : ($diasRestantesRecurso <= 3 ? 'bg-warning' : 'bg-info') }}">
                            <h5 class="text-white">Prazo para Recurso de Glosa</h5>
                            
                            @if ($recursoExpirado)
                                <div class="countdown-timer">
                                    <i class="fas fa-exclamation-triangle fa-3x text-white mb-2"></i>
                                    <h3 class="text-white">PRAZO EXPIRADO</h3>
                                    <p class="text-white">
                                        Excedido em {{ intval(abs($diasRestantesRecurso)) }} dias
                                    </p>
                                </div>
                            @else
                                <div class="countdown-timer">
                                    <i class="fas fa-hourglass-half fa-3x text-white mb-2"></i>
                                    <h2 class="text-white">{{ intval($diasRestantesRecurso) }}</h2>
                                    <p class="text-white">
                                        Dias Restantes
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($pacote->data_recebimento_recurso)
                        <!-- Prazo para Análise de Recurso (geralmente 30 dias internos) -->
                        @php
                            $dataLimiteAnalise = $pacote->data_recebimento_recurso ? (clone $pacote->data_recebimento_recurso)->addDays(30) : null;
                            $diasRestantesAnalise = $dataLimiteAnalise ? now()->diffInDays($dataLimiteAnalise, false) : null;
                            $analiseExpirada = $dataLimiteAnalise && now()->gt($dataLimiteAnalise);
                        @endphp
                        
                        <div class="countdown-box text-center p-3 mb-4 {{ $analiseExpirada ? 'bg-danger' : ($diasRestantesAnalise <= 5 ? 'bg-warning' : 'bg-success') }}">
                            <h5 class="text-white">Prazo Interno - Análise de Recurso</h5>
                            
                            @if ($analiseExpirada)
                                <div class="countdown-timer">
                                    <i class="fas fa-exclamation-triangle fa-3x text-white mb-2"></i>
                                    <h3 class="text-white">PRAZO EXPIRADO</h3>
                                    <p class="text-white">
                                        Excedido em {{ intval(abs($diasRestantesAnalise)) }} dias
                                    </p>
                                </div>
                            @else
                                <div class="countdown-timer">
                                    <i class="fas fa-hourglass-half fa-3x text-white mb-2"></i>
                                    <h2 class="text-white">{{ intval($diasRestantesAnalise) }}</h2>
                                    <p class="text-white">
                                        Dias Restantes
                                    </p>
                                </div>
                            @endif
                        </div>
                    @elseif ($pacote->valor_glosa == 0)
                        <div class="text-center p-3">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4>Sem Glosas Identificadas</h4>
                            <p class="text-muted">Nenhum prazo de glosa se aplica a este pacote.</p>
                        </div>
                    @elseif (!$pacote->data_notificacao_glosa)
                        <div class="text-center p-3">
                            <i class="fas fa-exclamation-circle fa-4x text-warning mb-3"></i>
                            <h4>Glosa Pendente de Notificação</h4>
                            <p>Realize a notificação da glosa para iniciar a contagem dos prazos.</p>
                        </div>
                    @endif

                    <!-- Alerta para ação pendente -->
                    @if ($pacote->valor_glosa > 0 && !$pacote->data_notificacao_glosa)
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-bell"></i> Ação Pendente: Notificar Existência de Glosa
                        </div>
                    @elseif ($pacote->data_notificacao_glosa && !$pacote->data_retirada_oficio)
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-bell"></i> Ação Pendente: Registrar Retirada de Ofício
                        </div>
                    @elseif ($pacote->data_retirada_oficio && !$pacote->data_recebimento_recurso && $pacote->estado_glosa == 'Aguardando Recurso de Glosa')
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-bell"></i> Ação Pendente: Registrar Recebimento de Recurso ou Recurso Não Recebido
                        </div>
                    @elseif ($pacote->data_recebimento_recurso && !isset($pacote->valor_deferido))
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-bell"></i> Ação Pendente: Registrar Resultado da Análise do Recurso
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Painel de Valores e Análise Financeira -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-gradient-success">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> Análise Financeira</h3>
                </div>
                <div class="card-body">
                    <!-- Gráfico de rosca para visualizar proporções -->
                    <div class="finance-chart text-center mb-4">
                        <canvas id="financePieChart" style="height: 200px; width: 200px; margin: 0 auto"></canvas>
                    </div>

                    <!-- Legenda e valores -->
                    <div class="finance-data">
                        <div class="row">
                            <div class="col-6">
                                <div class="finance-item mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><i class="fas fa-circle text-success"></i> Valor Pós-Lisura:</span>
                                        <span class="font-weight-bold">R$ {{ number_format($pacote->valor_pos_lisura, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="finance-item mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><i class="fas fa-circle text-danger"></i> Valor Glosa:</span>
                                        <span class="font-weight-bold">R$ {{ number_format($pacote->valor_glosa, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                @if ($pacote->data_recebimento_recurso && isset($pacote->valor_deferido))
                                <div class="finance-item mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><i class="fas fa-circle text-warning"></i> Valor Recursado:</span>
                                        <span class="font-weight-bold">R$ {{ number_format($pacote->valor_recurso_glosa ?? 0, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="finance-item mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><i class="fas fa-circle text-info"></i> Valor Deferido:</span>
                                        <span class="font-weight-bold">R$ {{ number_format($pacote->valor_deferido ?? 0, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Taxa de Deferimento (se aplicável) -->
                    @if ($pacote->data_recebimento_recurso && isset($pacote->valor_deferido) && isset($pacote->valor_recurso_glosa) && $pacote->valor_recurso_glosa > 0)
                        @php
                            $taxaDeferimento = ($pacote->valor_deferido / $pacote->valor_recurso_glosa) * 100;
                        @endphp
                        <div class="mt-4">
                            <p class="mb-1">Taxa de Deferimento</p>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: {{ $taxaDeferimento }}%">{{ number_format($taxaDeferimento, 1) }}%</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Linha do Tempo do Processo de Glosa -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title"><i class="fas fa-history"></i> Linha do Tempo do Processo de Glosa</h3>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-inverse">
                        <!-- Evento Inicial - Identificação da Glosa -->
                        <div class="time-label">
                            <span class="bg-danger">Início do Processo</span>
                        </div>
                        
                        <div>
                            <i class="fas fa-exclamation-triangle bg-danger"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $pacote->created_at->format('d/m/Y H:i') }}</span>
                                <h3 class="timeline-header"><a>Glosa Identificada</a></h3>
                                <div class="timeline-body">
                                    Foi identificada glosa no valor de R$ {{ number_format($pacote->valor_glosa, 2, ',', '.') }}, 
                                    representando {{ number_format(($pacote->valor_glosa / $pacote->valor_fatura) * 100, 2) }}% do valor original da fatura.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notificação de Glosa -->
                        @if ($pacote->data_notificacao_glosa)
                            <div>
                                <i class="fas fa-bell bg-blue"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $pacote->data_notificacao_glosa->format('d/m/Y H:i') }}</span>
                                    <h3 class="timeline-header"><a>Notificação de Existência de Glosa</a></h3>
                                    <div class="timeline-body">
                                        OCS/PSA {{ $pacote->ocsPsa->nome }} foi notificada sobre a existência de glosa.
                                        Data limite para retirada: {{ $pacote->data_limite_retirada ? $pacote->data_limite_retirada->format('d/m/Y') : 'Não definida' }}
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Retirada de Ofício -->
                        @if ($pacote->data_retirada_oficio)
                            <div>
                                <i class="fas fa-file-download bg-green"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $pacote->data_retirada_oficio->format('d/m/Y H:i') }}</span>
                                    <h3 class="timeline-header"><a>Retirada do Ofício de Glosa</a></h3>
                                    <div class="timeline-body">
                                        Ofício de glosa foi retirado pela OCS/PSA {{ $pacote->ocsPsa->nome }}.
                                        @php
                                            $diasAteRetirada = $pacote->data_retirada_oficio->diffInDays($pacote->data_notificacao_glosa);
                                        @endphp
                                        <br>
                                        <span class="text-muted">
                                            Tempo entre notificação e retirada: {{ $diasAteRetirada }} dias
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Recebimento de Recurso -->
                        @if ($pacote->data_recebimento_recurso)
                            <div>
                                <i class="fas fa-file-import bg-info"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $pacote->data_recebimento_recurso->format('d/m/Y H:i') }}</span>
                                    <h3 class="timeline-header"><a>Recebimento de Recurso de Glosa</a></h3>
                                    <div class="timeline-body">
                                        Recurso de glosa recebido da OCS/PSA {{ $pacote->ocsPsa->nome }}.
                                        @if(isset($pacote->valor_recurso_glosa))
                                            <br>Valor recursado: R$ {{ number_format($pacote->valor_recurso_glosa, 2, ',', '.') }}
                                        @endif
                                        
                                        @php
                                            $diasAteRecurso = $pacote->data_recebimento_recurso->diffInDays($pacote->data_retirada_oficio);
                                        @endphp
                                        <br>
                                        <span class="text-muted">
                                            Tempo entre retirada e recurso: {{ $diasAteRecurso }} dias
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Análise de Recurso -->
                        @if ($pacote->data_recebimento_recurso && isset($pacote->valor_deferido))
                            <div>
                                <i class="fas fa-balance-scale bg-purple"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ now()->format('d/m/Y') }}</span>
                                    <h3 class="timeline-header"><a>Análise do Recurso de Glosa</a></h3>
                                    <div class="timeline-body">
                                        Análise concluída com valor deferido de R$ {{ number_format($pacote->valor_deferido, 2, ',', '.') }}
                                        ({{ $pacote->valor_recurso_glosa > 0 ? number_format(($pacote->valor_deferido / $pacote->valor_recurso_glosa) * 100, 1) : 0 }}% do valor recursado).
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Evento de Recurso Não Recebido -->
                        @if ($pacote->estado_glosa == 'Recurso não recebido')
                            <div>
                                <i class="fas fa-times-circle bg-maroon"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ now()->format('d/m/Y') }}</span>
                                    <h3 class="timeline-header"><a>Recurso Não Recebido</a></h3>
                                    <div class="timeline-body">
                                        Não houve apresentação de recurso no prazo estabelecido. Processo encerrado.
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Evento Final / Próximos Passos -->
                        <div>
                            <i class="fas fa-flag-checkered bg-gray"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header"><a>Status Atual</a></h3>
                                <div class="timeline-body">
                                    @if ($pacote->valor_glosa == 0)
                                        <p>Este pacote não possui glosas identificadas.</p>
                                    @elseif (!$pacote->data_notificacao_glosa)
                                        <p>Aguardando notificação da existência de glosa à OCS/PSA.</p>
                                    @elseif (!$pacote->data_retirada_oficio)
                                        <p>Aguardando retirada do ofício de glosa pela OCS/PSA.</p>
                                    @elseif (!$pacote->data_recebimento_recurso && $pacote->estado_glosa == 'Aguardando Recurso de Glosa')
                                        <p>Aguardando apresentação de recurso pela OCS/PSA.</p>
                                    @elseif ($pacote->data_recebimento_recurso && !isset($pacote->valor_deferido))
                                        <p>Recurso em análise pela equipe de Glosa.</p>
                                    @elseif (isset($pacote->valor_deferido))
                                        <p>Processo de glosa finalizado com valor deferido.</p>
                                    @elseif ($pacote->estado_glosa == 'Recurso não recebido')
                                        <p>Processo de glosa finalizado sem apresentação de recurso.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Painel de Ações Disponíveis -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tools"></i> Ações Disponíveis</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if ($pacote->valor_glosa > 0 && !$pacote->data_notificacao_glosa && (Auth::user()->isAdmin() || Auth::user()->hasRole('glosa')))
                            <div class="col-md-3">
                                <a href="#" class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#modalNotificarGlosa" 
                                   data-id="{{ $pacote->id }}">
                                    <i class="fas fa-bell mr-2"></i> Notificar Existência de Glosa
                                </a>
                            </div>
                        @endif
                        
                        @if ($pacote->data_notificacao_glosa && !$pacote->data_retirada_oficio && (Auth::user()->isAdmin() || Auth::user()->hasRole('glosa')))
                            <div class="col-md-3">
                                <a href="#" class="btn btn-info btn-block mb-3" data-toggle="modal" data-target="#modalRetiradaOficioGlosa" 
                                   data-id="{{ $pacote->id }}">
                                    <i class="fas fa-file-download mr-2"></i> Registrar Retirada de Ofício
                                </a>
                            </div>
                        @endif
                        
                        @if ($pacote->data_retirada_oficio && !$pacote->data_recebimento_recurso && (Auth::user()->isAdmin() || Auth::user()->hasRole('protocolo')))
                            <div class="col-md-3">
                                <a href="#" class="btn btn-success btn-block mb-3" data-toggle="modal" data-target="#modalRecebimentoRecurso" 
                                   data-id="{{ $pacote->id }}">
                                    <i class="fas fa-file-import mr-2"></i> Registrar Recebimento de Recurso
                                </a>
                            </div>
                            
                            <div class="col-md-3">
                                <a href="#" class="btn btn-warning btn-block mb-3" data-toggle="modal" data-target="#modalRecursoNaoRecebido" 
                                   data-id="{{ $pacote->id }}">
                                    <i class="fas fa-times-circle mr-2"></i> Registrar Recurso Não Recebido
                                </a>
                            </div>
                        @endif
                        
                        @if ($pacote->data_recebimento_recurso && !isset($pacote->valor_deferido) && (Auth::user()->isAdmin() || Auth::user()->hasRole('glosa')))
                            <div class="col-md-3">
                                <a href="#" class="btn btn-purple btn-block mb-3" data-toggle="modal" data-target="#modalAnaliseRecurso" 
                                   data-id="{{ $pacote->id }}">
                                    <i class="fas fa-balance-scale mr-2"></i> Registrar Análise de Recurso
                                </a>
                            </div>
                        @endif
                        
                        <div class="col-md-3">
                            <a href="{{ route('pacotes.movimentacoes', $pacote->id) }}" class="btn btn-secondary btn-block mb-3">
                                <i class="fas fa-history mr-2"></i> Ver Histórico Completo
                            </a>
                        </div>
                        
                        <div class="col-md-3">
                            <a href="{{ route('pacotes.show', $pacote->id) }}" class="btn btn-outline-primary btn-block mb-3">
                                <i class="fas fa-eye mr-2"></i> Ver Detalhes do Pacote
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Estilos para o Timeline avançado */
        .timeline {
            margin: 0 0 30px 0;
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
            margin-right: 10px;
            margin-bottom: 15px;
            position: relative;
        }
        .time-label {
            margin-bottom: 15px;
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
            border-radius: 50%;
            text-align: center;
            left: 18px;
            top: 0;
            color: #fff;
        }
        .timeline-item {
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            border-radius: 3px;
            margin-left: 60px;
            margin-right: 15px;
            padding: 0;
            position: relative;
            background: #ffffff;
        }
        .timeline-item > .time {
            float: right;
            padding: 10px;
            font-size: 12px;
            color: #999;
        }
        .timeline-item > .timeline-header {
            margin: 0;
            padding: 10px;
            border-bottom: 1px solid #f4f4f4;
            font-size: 16px;
            line-height: 1.1;
        }
        .timeline-item > .timeline-body {
            padding: 10px;
        }

        /* Estilos para o status indicator */
        .status-indicator {
            padding: 15px 0;
        }
        .status-icon {
            display: inline-block;
            margin-bottom: 10px;
        }
        .status-text h4 {
            font-weight: 600;
            margin-bottom: 5px;
        }

        /* Estilos para os countdown boxes */
        .countdown-box {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .countdown-timer h2 {
            font-size: 3rem;
            font-weight: 700;
            margin: 10px 0;
        }

        /* Cores adicionais */
        .bg-purple {
            background-color: #6f42c1;
            color: white;
        }
        .text-purple {
            color: #6f42c1;
        }
        .bg-maroon {
            background-color: #d81b60;
            color: white;
        }
        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }
        .btn-purple:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
            color: white;
        }

        /* Correções para os info-boxes */
        .info-box {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .info-box-number {
            font-weight: 600;
        }

        /* Melhorias para os cards */
        .card {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .card-header {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function() {
            // Configuração do gráfico de análise financeira
            const ctx = document.getElementById('financePieChart');
            
            // Dados do pacote para o gráfico
            const valorPosLisura = {{ $pacote->valor_pos_lisura }};
            const valorGlosa = {{ $pacote->valor_glosa }};
            
            // Criar gráfico só se o elemento existir
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Valor Pós-Lisura', 'Valor Glosa'],
                        datasets: [{
                            data: [valorPosLisura, valorGlosa],
                            backgroundColor: ['#28a745', '#dc3545'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += new Intl.NumberFormat('pt-BR', { 
                                            style: 'currency', 
                                            currency: 'BRL' 
                                        }).format(context.raw);
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop