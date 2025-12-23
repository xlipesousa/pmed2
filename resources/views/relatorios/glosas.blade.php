@extends('adminlte::page')

@section('title', 'Relatório de Glosas')

@section('content_header')
    <h1>
        <i class="fas fa-exclamation-triangle"></i> Relatório de Glosas
        <small>Análise de Percentual de Glosas por OCS/PSA</small>
    </h1>
@stop

@section('content')
<div class="row">
    <!-- Filtros -->
    <div class="col-12">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Pesquisa</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('relatorios.glosas') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Período</label>
                                <input type="text" class="form-control" id="periodo" name="periodo" value="{{ request('periodo', now()->startOfYear()->format('d/m/Y') . ' - ' . now()->endOfYear()->format('d/m/Y')) }}">
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

    <!-- Tabela de Percentual de Glosas -->
    <div class="col-12">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-percentage"></i> Percentual de Glosas por OCS/PSA</h3>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>OCS/PSA</th>
                            <th>Valor Faturado (R$)</th>
                            <th>Valor Glosado (R$)</th>
                            <th>Percentual de Glosa (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($percentuaisGlosa as $glosa)
                            <tr>
                                <td>{{ $glosa['nome'] }}</td>
                                <td>R$ {{ number_format($glosa['valor_faturado'], 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($glosa['valor_glosado'], 2, ',', '.') }}</td>
                                <td>{{ number_format($glosa['percentual_glosa'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
    });
</script>
@stop
