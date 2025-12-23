@extends('adminlte::page')

@section('title', 'Relatório de Performance e Produtividade')

@section('content_header')
    <h1>
        <i class="fas fa-tachometer-alt"></i> Relatório de Performance e Produtividade
        <small>Análise de Desempenho e Eficiência</small>
    </h1>
@stop

@section('content')
<div class="row">
    <!-- Filtros -->
    <div class="col-12">
        <div class="card card-info card-outline collapsed-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Pesquisa</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('relatorios.performance') }}" id="form-filtros">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Período</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="periodo" name="periodo">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Setor</label>
                                <select class="form-control" name="setor">
                                    <option value="">Todos</option>
                                    @foreach($setores as $setor)
                                        <option value="{{ $setor }}" {{ request('setor') == $setor ? 'selected' : '' }}>{{ $setor }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Usuário</label>
                                <select class="form-control select2" name="usuario_id">
                                    <option value="">Todos</option>
                                    @foreach(App\Models\User::orderBy('name')->get() as $usuario)
                                        <option value="{{ $usuario->id }}" {{ request('usuario_id') == $usuario->id ? 'selected' : '' }}>
                                            {{ $usuario->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="{{ route('relatorios.performance') }}" class="btn btn-default">
                                    <i class="fas fa-eraser"></i> Limpar
                                </a>
                                <button type="button" class="btn btn-success" id="btn-exportar">
                                    <i class="fas fa-file-excel"></i> Exportar
                                </button>
                                <button type="button" class="btn btn-info" id="btn-imprimir">
                                    <i class="fas fa-print"></i> Imprimir
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- KPIs Performance -->
    <div class="col-12">
        <div class="row">
            <!-- Tempo Médio Total -->
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tempo Médio Total</span>
                        <span class="info-box-number">{{ number_format(array_sum($tempoMedioPorSetor) / count(array_filter($tempoMedioPorSetor)), 1) }} dias</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            Tempo médio de processamento completo
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Total de Pacotes Processados -->
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pacotes Processados</span>
                        <span class="info-box-number">{{ number_format(array_sum($pacotesPorSetor)) }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            Total de pacotes no período
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Setor Mais Lento -->
            @php
                $setorMaisLento = array_search(max($tempoMedioPorSetor), $tempoMedioPorSetor);
                $tempoMaisLento = max($tempoMedioPorSetor);
            @endphp
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Setor Mais Lento</span>
                        <span class="info-box-number">{{ $setorMaisLento }} ({{ number_format($tempoMaisLento, 1) }} dias)</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            Setor com maior tempo médio
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Setor Com Mais Gargalos -->
            @php
                $setorGargalo = array_search(max(array_column($gargalos, 'total_problemas')), array_column($gargalos, 'total_problemas'));
                $totalGargalos = max(array_column($gargalos, 'total_problemas'));
            @endphp
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Setor com Mais Gargalos</span>
                        <span class="info-box-number">{{ $setorGargalo }} ({{ number_format($totalGargalos) }} pacotes)</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            Maior quantidade de pacotes atrasados/críticos
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Performance -->
    <div class="col-md-6">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock"></i> Tempo Médio por Setor</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="tempoMedioChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                <div class="mt-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Setor</th>
                                <th>Tempo Médio (dias)</th>
                                <th>Pacotes Processados</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($setores as $setor)
                            <tr>
                                <td>{{ $setor }}</td>
                                <td>{{ number_format($tempoMedioPorSetor[$setor], 1) }}</td>
                                <td>{{ number_format($pacotesPorSetor[$setor]) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Produtividade Mensal</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="produtividadeChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                <div class="mt-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Mês</th>
                                <th>Pacotes Processados</th>
                                <th>Variação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $mesAnterior = 0; @endphp
                            @foreach($meses as $i => $mes)
                            @php
                                $variacao = $mesAnterior > 0 ? (($produtividadeMensal[$i] - $mesAnterior) / $mesAnterior) * 100 : 0;
                                $mesAnterior = $produtividadeMensal[$i];
                            @endphp
                            <tr>
                                <td>{{ $mes }}</td>
                                <td>{{ number_format($produtividadeMensal[$i]) }}</td>
                                <td>
                                    @if($i > 0)
                                        <span class="{{ $variacao >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($variacao, 1) }}%
                                            <i class="fas fa-{{ $variacao >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Volume por Tipo de Pacote</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="volumeTipoChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                <div class="mt-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tipo de Pacote</th>
                                <th>Quantidade</th>
                                <th>Percentual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalVolume = array_sum($volumePorTipo); @endphp
                            @foreach($volumePorTipoLabels as $i => $tipo)
                            <tr>
                                <td>{{ $tipo }}</td>
                                <td>{{ number_format($volumePorTipo[$i]) }}</td>
                                <td>{{ number_format(($volumePorTipo[$i] / $totalVolume) * 100, 1) }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Gargalos por Setor</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="gargalosChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                <div class="mt-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Setor</th>
                                <th>Pacotes Atrasados</th>
                                <th>Pacotes Críticos</th>
                                <th>Total Problemas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($setores as $setor)
                            <tr>
                                <td>{{ $setor }}</td>
                                <td>{{ number_format($gargalos[$setor]['atrasados']) }}</td>
                                <td>{{ number_format($gargalos[$setor]['criticos']) }}</td>
                                <td>{{ number_format($gargalos[$setor]['total_problemas']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance por Usuário -->
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Performance por Usuário</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabela-usuarios" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Movimentações</th>
                                <th>Setor</th>
                                <th>Média Diária</th>
                                <th>% do Total</th>
                                <th>Tendência</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuariosMovimentacao as $usuario)
                            @php
                                $diasUteis = \Carbon\Carbon::parse(request('periodo_inicio', \Carbon\Carbon::now()->subDays(30)))->diffInWeekdays(\Carbon\Carbon::parse(request('periodo_fim', \Carbon\Carbon::now())));
                                $mediaDiaria = $diasUteis > 0 ? $usuario->total_movimentacoes / $diasUteis : 0;
                                $percentualTotal = array_sum(array_column($usuariosMovimentacao->toArray(), 'total_movimentacoes')) > 0 ? 
                                    ($usuario->total_movimentacoes / array_sum(array_column($usuariosMovimentacao->toArray(), 'total_movimentacoes'))) * 100 : 0;
                                
                                // Simulando dados de tendência para o exemplo
                                $tendencias = ['em-alta', 'estável', 'em-queda'];
                                $tendencia = $tendencias[array_rand($tendencias)];
                            @endphp
                            <tr>
                                <td>{{ $usuario->usuario->name }}</td>
                                <td>{{ number_format($usuario->total_movimentacoes) }}</td>
                                <td>{{ $usuario->usuario->setor ?? 'Não definido' }}</td>
                                <td>{{ number_format($mediaDiaria, 1) }}</td>
                                <td>{{ number_format($percentualTotal, 1) }}%</td>
                                <td>
                                    @if($tendencia == 'em-alta')
                                        <span class="text-success"><i class="fas fa-arrow-up"></i> Em alta</span>
                                    @elseif($tendencia == 'em-queda')
                                        <span class="text-danger"><i class="fas fa-arrow-down"></i> Em queda</span>
                                    @else
                                        <span class="text-muted"><i class="fas fa-arrows-alt-h"></i> Estável</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function() {
        // Inicialização do DateRangePicker
        $('#periodo').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'De',
                toLabel: 'Até',
                customRangeLabel: 'Personalizado',
                daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                firstDay: 1
            },
            startDate: moment().subtract(30, 'days'),
            endDate: moment(),
            ranges: {
               'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
               'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
               'Este Mês': [moment().startOf('month'), moment().endOf('month')],
               'Mês Passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
               'Último Trimestre': [moment().subtract(3, 'month').startOf('month'), moment().endOf('month')]
            }
        });
        
        // Inicialização do Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: "Selecione uma opção",
            allowClear: true
        });
        
        // Inicialização do DataTables
        var table = $('#tabela-usuarios').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
            },
            responsive: true,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn-success',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn-info',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ]
        });
        
        // Botões de exportação
        $('#btn-exportar').click(function() {
            table.button('.buttons-excel').trigger();
        });
        
        $('#btn-imprimir').click(function() {
            table.button('.buttons-print').trigger();
        });

        // Gráfico de Tempo Médio por Setor
        const tempoMedioCtx = document.getElementById('tempoMedioChart').getContext('2d');
        const tempoMedioChart = new Chart(tempoMedioCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($setores) !!},
                datasets: [{
                    label: 'Tempo Médio (dias)',
                    data: [
                        @foreach($setores as $setor)
                        {{ $tempoMedioPorSetor[$setor] }},
                        @endforeach
                    ],
                    backgroundColor: '#17a2b8',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de Produtividade Mensal
        const produtividadeCtx = document.getElementById('produtividadeChart').getContext('2d');
        const produtividadeChart = new Chart(produtividadeCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($meses) !!},
                datasets: [{
                    label: 'Pacotes Processados',
                    data: {!! json_encode($produtividadeMensal) !!},
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de Volume por Tipo
        const volumeTipoCtx = document.getElementById('volumeTipoChart').getContext('2d');
        const volumeTipoChart = new Chart(volumeTipoCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($volumePorTipoLabels) !!},
                datasets: [{
                    data: {!! json_encode($volumePorTipo) !!},
                    backgroundColor: [
                        '#007bff',
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#6c757d',
                        '#17a2b8',
                        '#fd7e14',
                        '#20c997'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Gráfico de Gargalos
        const gargalosCtx = document.getElementById('gargalosChart').getContext('2d');
        const gargalosChart = new Chart(gargalosCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($setores) !!},
                datasets: [
                    {
                        label: 'Pacotes Atrasados',
                        data: [
                            @foreach($setores as $setor)
                            {{ $gargalos[$setor]['atrasados'] }},
                            @endforeach
                        ],
                        backgroundColor: '#ffc107',
                        borderWidth: 1
                    },
                    {
                        label: 'Pacotes Críticos',
                        data: [
                            @foreach($setores as $setor)
                            {{ $gargalos[$setor]['criticos'] }},
                            @endforeach
                        ],
                        backgroundColor: '#dc3545',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@stop