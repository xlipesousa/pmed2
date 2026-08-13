@extends('adminlte::page')

@section('title', 'Central de Relatórios')

@section('content_header')
    <h1>
        <i class="fas fa-chart-bar"></i> Central de Relatórios
        <small>Dashboard de Monitoramento</small>
    </h1>
@stop

@section('content')
<div class="row">
    <!-- Relatórios Pré-formatados (Acesso Rápido) -->
    <div class="col-12 mb-4">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Relatórios Pré-formatados</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('relatorios.financeiro') }}" class="btn btn-app bg-success">
                            <i class="fas fa-dollar-sign"></i>
                            <div class="btn-title">Financeiro</div>
                            <div class="btn-description text-white">Análise de valores e fluxo financeiro</div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('relatorios.status-pacotes') }}" class="btn btn-app bg-primary">
                            <i class="fas fa-boxes"></i>
                            <div class="btn-title">Status de Pacotes</div>
                            <div class="btn-description text-white">Acompanhamento e monitoramento</div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('relatorios.glosas') }}" class="btn btn-app bg-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div class="btn-title">Glosas</div>
                            <div class="btn-description text-white">Análise e gestão de glosas</div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('relatorios.ocspsa') }}" class="btn btn-app bg-info">
                            <i class="fas fa-hospital"></i>
                            <div class="btn-title">OCS/PSA</div>
                            <div class="btn-description text-white">Prestadores e organizações</div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('relatorios.prazo-recurso') }}" class="btn btn-app bg-danger">
                            <i class="far fa-clock"></i>
                            <div class="btn-title">Prazo de Recurso</div>
                            <div class="btn-description text-white">Pacotes aguardando recurso vencido</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Painel Superior - KPIs Principais -->
    <div class="col-12">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ App\Models\Pacote::count() }}</h3>
                        <p>Total de Pacotes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <a href="{{ route('relatorios.status-pacotes') }}" class="small-box-footer">
                        Mais informações <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>R$ {{ number_format(App\Models\Pacote::sum('valor_fatura'), 2, ',', '.') }}</h3>
                        <p>Valor Total de Faturas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <a href="{{ route('relatorios.financeiro') }}" class="small-box-footer">
                        Mais informações <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>
                            @php
                                $valorGlosa = App\Models\Pacote::sum('valor_glosa');
                                $valorFatura = App\Models\Pacote::sum('valor_fatura');
                                $percentualGlosa = ($valorFatura > 0) ? round(($valorGlosa / $valorFatura) * 100, 2) : 0;
                                echo $percentualGlosa . '%';
                            @endphp
                        </h3>
                        <p>Taxa de Glosa</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <a href="{{ route('relatorios.glosas') }}" class="small-box-footer">
                        Mais informações <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ App\Models\OcsPsa::count() }}</h3>
                        <p>OCS/PSA Cadastradas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <a href="{{ route('relatorios.ocspsa') }}" class="small-box-footer">
                        Mais informações <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Relatórios Disponíveis -->
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Status de Pacotes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="statusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
                <div class="mt-4">
                    <div class="text-center">
                        <span class="badge bg-info px-3 py-2 mr-2">Protocolo: {{ App\Models\Pacote::where('localizacao_atual', 'Protocolo')->count() }}</span>
                        <span class="badge bg-success px-3 py-2 mr-2">Lisura: {{ App\Models\Pacote::where('localizacao_atual', 'Lisura')->count() }}</span>
                        <span class="badge bg-warning px-3 py-2 mr-2">SIRE: {{ App\Models\Pacote::where('localizacao_atual', 'SIRE')->count() }}</span>
                        <span class="badge bg-danger px-3 py-2 mr-2">Glosa: {{ App\Models\Pacote::where('localizacao_atual', 'Glosa')->count() }}</span>
                        <span class="badge bg-primary px-3 py-2">Arquivo: {{ App\Models\Pacote::where('localizacao_atual', 'Arquivo')->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('relatorios.status-pacotes') }}" class="btn btn-primary">
                    <i class="fas fa-search"></i> Análise Detalhada
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-dollar-sign"></i> Resumo Financeiro</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="financeiroChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
                <div class="mt-4">
                    <div class="row">
                        <div class="col-6">
                            <div class="description-block border-right">
                                <h5 class="description-header">R$ {{ number_format(App\Models\Pacote::sum('valor_pago'), 2, ',', '.') }}</h5>
                                <span class="description-text text-success">VALOR IMPLANTADO</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <h5 class="description-header">R$ {{ number_format(App\Models\Pacote::sum('valor_glosa'), 2, ',', '.') }}</h5>
                                <span class="description-text text-danger">VALOR GLOSADO</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('relatorios.financeiro') }}" class="btn btn-success">
                    <i class="fas fa-search"></i> Análise Detalhada
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Gestão de Glosas</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart">
                    <!-- Canvas para o gráfico de estados de glosa -->
                    <canvas id="glosaChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
                <div class="mt-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Percentual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Obter contagem de pacotes por estado de glosa
                                $estadosGlosa = [
                                    'Glosa não identificada' => App\Models\Pacote::where('estado_glosa', 'Glosa não identificada')->count(),
                                    'Glosa identificada' => App\Models\Pacote::where('estado_glosa', 'Glosa identificada')->count(),
                                    'Existência de Glosa Notificada' => App\Models\Pacote::where('estado_glosa', 'Existência de Glosa Notificada')->count(),
                                    'OCS/PSA retirou Ofício de Glosa' => App\Models\Pacote::where('estado_glosa', 'OCS/PSA retirou Ofício de Glosa')->count(),
                                    'Aguardando recurso de Glosa' => App\Models\Pacote::where('estado_glosa', 'Aguardando recurso de Glosa')->count(),
                                    'Recurso recebido' => App\Models\Pacote::where('estado_glosa', 'Recurso recebido')->count(),
                                    'Recurso não recebido' => App\Models\Pacote::where('estado_glosa', 'Recurso não recebido')->count(),
                                    'Recurso indeferido' => App\Models\Pacote::where('estado_glosa', 'Recurso indeferido')->count(),
                                    'Recurso deferido' => App\Models\Pacote::where('estado_glosa', 'Recurso deferido')->count()
                                ];
                                $totalPacotes = array_sum($estadosGlosa);
                            @endphp

                            @foreach($estadosGlosa as $estado => $total)
                                @if($total > 0)
                                    <tr>
                                        <td>{{ $estado }}</td>
                                        <td class="text-right">{{ $total }}</td>
                                        <td class="text-right">{{ $totalPacotes > 0 ? number_format(($total / $totalPacotes) * 100, 1) : 0 }}%</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('relatorios.glosas') }}" class="btn btn-warning">
                    <i class="fas fa-chart-pie"></i> Ver Relatório Detalhado
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-hospital"></i> Top 5 OCS/PSA</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="ocspsaChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
                <div class="mt-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>OCS/PSA</th>
                                <th class="text-center">Qtde. Pacotes</th>
                                <th class="text-right">Valor Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $topOcsPsa = DB::table('pacotes')
                                    ->join('ocs_psa', 'pacotes.ocs_psa_id', '=', 'ocs_psa.id')
                                    ->select('ocs_psa.id', 'ocs_psa.nome', 
                                            DB::raw('COUNT(*) as total_pacotes'),
                                            DB::raw('SUM(pacotes.valor_fatura) as valor_total'))
                                    ->groupBy('ocs_psa.id', 'ocs_psa.nome')
                                    ->orderBy('total_pacotes', 'desc')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            
                            @foreach($topOcsPsa as $ocs)
                                <tr>
                                    <td>{{ $ocs->nome }}</td>
                                    <td class="text-center">{{ $ocs->total_pacotes }}</td>
                                    <td class="text-right">R$ {{ number_format($ocs->valor_total, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('relatorios.ocspsa') }}" class="btn btn-info">
                    <i class="fas fa-search"></i> Análise Detalhada
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Estilo melhorado para os cards de relatórios */
    .btn-app {
        height: 110px; /* Aumentado de 85px */
        min-width: 100%;
        padding: 18px 5px;
        margin-bottom: 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .btn-app:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    .btn-app i {
        font-size: 32px; /* Aumentado de 24px */
        margin-bottom: 12px;
    }
    
    .btn-app .btn-title {
        font-size: 18px; /* Novo tamanho para o título */
        font-weight: 500;
        margin-bottom: 8px;
    }
    
    .btn-app .btn-description {
        font-size: 14px; /* Aumentado o tamanho da descrição */
        opacity: 0.9;
        line-height: 1.4;
    }
    
    /* Resto dos estilos existentes */
    .status-pendente td {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    /* ... outros estilos ... */
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    $(function() {
        // Gráfico de Status
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Protocolo', 'Lisura', 'SIRE', 'Glosa', 'Arquivo'],
                datasets: [{
                    data: [
                        {{ App\Models\Pacote::where('localizacao_atual', 'Protocolo')->count() }},
                        {{ App\Models\Pacote::where('localizacao_atual', 'Lisura')->count() }},
                        {{ App\Models\Pacote::where('localizacao_atual', 'SIRE')->count() }},
                        {{ App\Models\Pacote::where('localizacao_atual', 'Glosa')->count() }},
                        {{ App\Models\Pacote::where('localizacao_atual', 'Arquivo')->count() }}
                    ],
                    backgroundColor: [
                        '#17a2b8', // Info
                        '#28a745', // Success
                        '#ffc107', // Warning
                        '#dc3545', // Danger
                        '#007bff'  // Primary
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                },
                cutout: '60%'
            }
        });
        
        // Gráfico Financeiro
        const finCtx = document.getElementById('financeiroChart').getContext('2d');
        const finChart = new Chart(finCtx, {
            type: 'bar',
            data: {
                labels: ['Faturado', 'Implantado', 'Glosado', 'Pendente'],
                datasets: [{
                    label: 'Valores (R$)',
                    data: [
                        {{ App\Models\Pacote::sum('valor_fatura') }},
                        {{ App\Models\Pacote::sum('valor_pago') }},
                        {{ App\Models\Pacote::sum('valor_glosa') }},
                        {{ App\Models\Pacote::whereRaw('valor_fatura > IFNULL(valor_pago, 0) + IFNULL(valor_glosa, 0)')->sum(DB::raw('valor_fatura - IFNULL(valor_pago, 0) - IFNULL(valor_glosa, 0)')) }}
                    ],
                    backgroundColor: [
                        'rgba(23, 162, 184, 0.5)',
                        'rgba(40, 167, 69, 0.5)',
                        'rgba(220, 53, 69, 0.5)',
                        'rgba(255, 193, 7, 0.5)'
                    ],
                    borderColor: [
                        'rgba(23, 162, 184, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.raw;
                                return 'R$ ' + value.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                }
            }
        });
        
        // Gráfico de Glosas
        const glosaCtx = document.getElementById('glosaChart').getContext('2d');
        const glosaChart = new Chart(glosaCtx, {
            type: 'pie',
            data: {
                labels: [
                    @foreach($estadosGlosa as $estado => $total)
                        @if($total > 0) '{{ $estado }}', @endif
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach($estadosGlosa as $total)
                            @if($total > 0) {{ $total }}, @endif
                        @endforeach
                    ],
                    backgroundColor: [
                        '#f6c23e', // Amarelo para "Glosa não identificada"
                        '#e74a3b', // Vermelho para "Glosa identificada" 
                        '#f39c12', // Laranja para "Existência de Glosa Notificada"
                        '#17a2b8', // Ciano para "OCS/PSA retirou Ofício de Glosa"
                        '#36b9cc', // Azul claro para "Aguardando recurso de Glosa"
                        '#4e73df', // Azul para "Recurso recebido"
                        '#6f42c1', // Roxo para "Recurso não recebido"
                        '#e74a3b', // Vermelho para "Recurso indeferido"
                        '#1cc88a'  // Verde para "Recurso deferido"
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((acc, curr) => acc + curr, 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // Gráfico Top 5 OCS/PSA
        const ocspsaCtx = document.getElementById('ocspsaChart').getContext('2d');
        const ocspsaChart = new Chart(ocspsaCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($topOcsPsa as $ocs)
                        '{{ $ocs->nome }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Quantidade de Pacotes',
                    data: [
                        @foreach($topOcsPsa as $ocs)
                            {{ $ocs->total_pacotes }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(0, 123, 255, 0.5)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@stop