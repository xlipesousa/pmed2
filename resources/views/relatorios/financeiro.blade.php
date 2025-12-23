@extends('adminlte::page')

@section('title', 'Relatório Financeiro')

@section('content_header')
    <h1>
        <i class="fas fa-dollar-sign"></i> Relatório Financeiro
        <small>Análise de Valores e Desempenho Financeiro</small>
    </h1>
@stop

@section('content')
<div class="row">
    <!-- Filtros -->
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Pesquisa</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('relatorios.financeiro') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Período</label>
                                <input type="text" class="form-control" id="periodo" name="periodo" value="{{ request('periodo', now()->startOfMonth()->format('d/m/Y') . ' - ' . now()->endOfMonth()->format('d/m/Y')) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>OCS/PSA</label>
                                <select class="form-control select2" name="ocs_psa_id">
                                    <option value="">Todos</option>
                                    @foreach($ocsPsaList as $ocspsa)
                                        <option value="{{ $ocspsa->id }}" {{ request('ocs_psa_id') == $ocspsa->id ? 'selected' : '' }}>
                                            {{ $ocspsa->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="submit" class="btn btn-primary mt-4">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- KPIs Financeiros -->
    <div class="col-12">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>R$ {{ number_format($totalFaturado, 2, ',', '.') }}</h3>
                        <p>Total Faturado</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>R$ {{ number_format($totalGlosado, 2, ',', '.') }}</h3>
                        <p>Total Glosado</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>R$ {{ number_format($totalPago, 2, ',', '.') }}</h3>
                        <p>Total Implantado</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>R$ {{ number_format($totalPendente, 2, ',', '.') }}</h3>
                        <p>Total Pendente</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Financeiros -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> Distribuição Financeira</h3>
                </div>
                <div class="card-body">
                    <canvas id="graficoDistribuicaoFinanceira" style="min-height: 250px; height: 250px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Evolução Mensal</h3>
                </div>
                <div class="card-body">
                    <canvas id="graficoEvolucaoMensal" style="min-height: 250px; height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(function() {
        // Inicializar Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: "Selecione uma opção",
            allowClear: true
        });

        // Inicializar DateRangePicker
        $('#periodo').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                firstDay: 1
            }
        });

        // Gráfico de Distribuição Financeira
        const ctxDistribuicao = document.getElementById('graficoDistribuicaoFinanceira').getContext('2d');
        new Chart(ctxDistribuicao, {
            type: 'pie',
            data: {
                labels: ['Faturado', 'Glosado', 'Implantado', 'Pendente'],
                datasets: [{
                    data: [{{ $totalFaturado }}, {{ $totalGlosado }}, {{ $totalPago }}, {{ $totalPendente }}],
                    backgroundColor: ['#17a2b8', '#ffc107', '#28a745', '#dc3545']
                }]
            }
        });

        // Gráfico de Evolução Mensal
        const ctxEvolucao = document.getElementById('graficoEvolucaoMensal').getContext('2d');
        new Chart(ctxEvolucao, {
            type: 'line',
            data: {
                labels: @json($evolucaoMensal['meses']),
                datasets: [{
                    label: 'Faturado',
                    data: @json($evolucaoMensal['faturado']),
                    borderColor: '#17a2b8',
                    fill: false
                }, {
                    label: 'Implantado',
                    data: @json($evolucaoMensal['pago']),
                    borderColor: '#28a745',
                    fill: false
                }]
            }
        });
    });
</script>
@stop
