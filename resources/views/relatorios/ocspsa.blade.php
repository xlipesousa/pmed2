@extends('adminlte::page')

@section('title', 'Relatório de OCS/PSA')

@section('content_header')
    <h1>
        <i class="fas fa-hospital"></i> Relatório de OCS/PSA
        <small>Análise de Prestadores e Organizações</small>
    </h1>
@stop

@section('content')
@php
    // Definir variáveis necessárias
    $totalOcsPsa = App\Models\OcsPsa::count();
    $totalFaturado = App\Models\Pacote::sum('valor_fatura') ?? 0;
    $valorGlosa = App\Models\Pacote::sum('valor_glosa') ?? 0;
    $valorDeferido = App\Models\Pacote::sum('valor_deferido') ?? 0;
    
    // Valores médios e métricas
    $taxaMediaGlosa = ($totalFaturado > 0) ? ($valorGlosa / $totalFaturado) * 100 : 0;
    $taxaMediaRecuperacao = ($valorGlosa > 0) ? ($valorDeferido / $valorGlosa) * 100 : 0;
@endphp

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
                <form method="GET" action="{{ route('relatorios.ocspsa') }}" id="form-filtros">
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
                                    @foreach(App\Models\OcsPsa::orderBy('nome')->get() as $ocspsa)
                                        <option value="{{ $ocspsa->id }}" {{ request('ocs_psa_id') == $ocspsa->id ? 'selected' : '' }}>
                                            {{ $ocspsa->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo de Pacote</label>
                                <select class="form-control select2" name="tipo_pacote[]" multiple>
                                    @foreach(App\Models\TipoPacote::orderBy('nome')->get() as $tipo)
                                        <option value="{{ $tipo->id }}" {{ in_array($tipo->id, request('tipo_pacote', [])) ? 'selected' : '' }}>
                                            {{ $tipo->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('relatorios.ocspsa') }}" class="btn btn-secondary">
                                <i class="fas fa-eraser"></i> Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- KPIs - Resumo Consolidado -->
    <div class="col-12">
        <div class="row">
            <!-- Total de OCS/PSA -->
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalOcsPsa }}</h3>
                        <p>Total de OCS/PSA</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hospital"></i>
                    </div>
                </div>
            </div>

            <!-- Total Faturado -->
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>R$ {{ number_format($totalFaturado, 2, ',', '.') }}</h3>
                        <p>Total Faturado</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>

            <!-- Taxa Média de Glosa -->
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($taxaMediaGlosa, 1) }}<sup style="font-size: 20px">%</sup></h3>
                        <p>Taxa Média de Glosa</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            <!-- Taxa Média de Recuperação -->
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($taxaMediaRecuperacao, 1) }}<sup style="font-size: 20px">%</sup></h3>
                        <p>Taxa Média de Recuperação</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-undo"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabelas e Gráficos -->
    <div class="row">
        <!-- Top OCS/PSA por Valor -->
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Top 10 OCS/PSA por Valor</h3>
                </div>
                <div class="card-body">
                    <canvas id="valorOcsPsaChart" style="min-height: 250px; height: 250px;"></canvas>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>OCS/PSA</th>
                                    <th>Valor Total</th>
                                    <th>% do Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalGeral = $topOcsPsaValor->sum('valor'); @endphp
                                @foreach($topOcsPsaValor as $ocspsa)
                                <tr>
                                    <td>{{ $ocspsa['nome'] }}</td>
                                    <td>R$ {{ number_format($ocspsa['valor'], 2, ',', '.') }}</td>
                                    <td>{{ number_format(($ocspsa['valor'] / $totalGeral) * 100, 1) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top OCS/PSA por Volume -->
        <div class="col-md-6">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-boxes"></i> Top 10 OCS/PSA por Volume</h3>
                </div>
                <div class="card-body">
                    <canvas id="volumeOcsPsaChart" style="min-height: 250px; height: 250px;"></canvas>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>OCS/PSA</th>
                                    <th>Quantidade de Pacotes</th>
                                    <th>% do Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalVolume = $topOcsPsaVolume->sum('quantidade'); @endphp
                                @foreach($topOcsPsaVolume as $ocspsa)
                                <tr>
                                    <td>{{ $ocspsa['nome'] }}</td>
                                    <td>{{ number_format($ocspsa['quantidade']) }}</td>
                                    <td>{{ number_format(($ocspsa['quantidade'] / $totalVolume) * 100, 1) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
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

        // Gráficos
        const valorCtx = document.getElementById('valorOcsPsaChart').getContext('2d');
        const volumeCtx = document.getElementById('volumeOcsPsaChart').getContext('2d');

        // Gráfico de Valor
        new Chart(valorCtx, {
            type: 'bar',
            data: {
                labels: @json($topOcsPsaValor->pluck('nome')),
                datasets: [{
                    label: 'Valor Total (R$)',
                    data: @json($topOcsPsaValor->pluck('valor')),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            }
        });

        // Gráfico de Volume
        new Chart(volumeCtx, {
            type: 'bar',
            data: {
                labels: @json($topOcsPsaVolume->pluck('nome')),
                datasets: [{
                    label: 'Quantidade de Pacotes',
                    data: @json($topOcsPsaVolume->pluck('quantidade')),
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            }
        });
    });
</script>
@php $totalGlosa = $topOcsPsaGlosa->sum('valor_glosado'); @endphp
@php $totalRecuperacao = $topOcsPsaRecuperacao->sum('valor_recuperado'); @endphp
@php $totalVolume = $topOcsPsaVolume->sum('quantidade'); @endphp
@stop