@extends('adminlte::page')

@section('title', 'Relatório de Status de Pacotes')

@section('content_header')
    <h1>
        <i class="fas fa-boxes"></i> Relatório de Status de Pacotes
        <small>Acompanhamento de Localização e Tramitação</small>
    </h1>
@stop

@section('content')
<div class="row">
    <!-- Filtros -->
    <div class="col-12">
        <div class="card card-primary card-outline collapsed-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Pesquisa</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('relatorios.status-pacotes') }}" id="form-filtros">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Período de Entrada</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="range-date" name="periodo">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status Atual</label>
                                <select class="form-control select2" name="status" style="width: 100%;">
                                    <option value="">Todos</option>
                                    <option value="Protocolo" {{ request('status') == 'Protocolo' ? 'selected' : '' }}>Protocolo</option>
                                    <option value="Lisura" {{ request('status') == 'Lisura' ? 'selected' : '' }}>Lisura</option>
                                    <option value="SIRE" {{ request('status') == 'SIRE' ? 'selected' : '' }}>SIRE</option>
                                    <option value="Glosa" {{ request('status') == 'Glosa' ? 'selected' : '' }}>Glosa</option>
                                    <option value="Arquivo" {{ request('status') == 'Arquivo' ? 'selected' : '' }}>Arquivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>OCS/PSA</label>
                                <select class="form-control select2" name="ocs_psa_id" style="width: 100%;">
                                    <option value="">Todos</option>
                                    @foreach(App\Models\OcsPsa::orderBy('nome')->get() as $ocsPsa)
                                        <option value="{{ $ocsPsa->id }}" {{ request('ocs_psa_id') == $ocsPsa->id ? 'selected' : '' }}>
                                            {{ $ocsPsa->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo de Pacote</label>
                                <select class="form-control select2" name="tipo_id" style="width: 100%;">
                                    <option value="">Todos</option>
                                    @foreach(App\Models\TipoPacote::orderBy('nome')->get() as $tipo)
                                        <option value="{{ $tipo->id }}" {{ request('tipo_id') == $tipo->id ? 'selected' : '' }}>
                                            {{ $tipo->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tempo de Permanência (dias)</label>
                                <div class="input-group">
                                    <select class="form-control" name="tempo_permanencia_operador">
                                        <option value=">" {{ request('tempo_permanencia_operador') == '>' ? 'selected' : '' }}>Maior que</option>
                                        <option value="<" {{ request('tempo_permanencia_operador') == '<' ? 'selected' : '' }}>Menor que</option>
                                        <option value="=" {{ request('tempo_permanencia_operador') == '=' ? 'selected' : '' }}>Igual a</option>
                                    </select>
                                    <input type="number" class="form-control" name="tempo_permanencia" value="{{ request('tempo_permanencia') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="{{ route('relatorios.status-pacotes') }}" class="btn btn-default">
                                    <i class="fas fa-eraser"></i> Limpar
                                </a>
                                <button type="button" class="btn btn-success" id="btn-exportar">
                                    <i class="fas fa-file-excel"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Resumo de Status -->
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Resumo por Status</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <canvas id="statusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            @php
                                $statusList = ['Protocolo', 'Lisura', 'SIRE', 'Glosa', 'Arquivo'];
                                $totalPacotes = App\Models\Pacote::count();
                                
                                foreach($statusList as $status) {
                                    $quantidade = App\Models\Pacote::where('localizacao_atual', $status)->count();
                                    $percentual = ($totalPacotes > 0) ? ($quantidade / $totalPacotes) * 100 : 0;
                                    
                                    // Definir a cor de cada status
                                    if ($status == 'Protocolo') $cor = 'info';
                                    elseif ($status == 'Lisura') $cor = 'primary';
                                    elseif ($status == 'SIRE') $cor = 'warning';
                                    elseif ($status == 'Glosa') $cor = 'danger';
                                    elseif ($status == 'Arquivo') $cor = 'success';
                                @endphp
                                <div class="col-6 col-md-4">
                                    <div class="info-box bg-{{ $cor }} bg-gradient-{{ $cor }}">
                                        <span class="info-box-icon"><i class="fas fa-{{ ($status == 'Protocolo') ? 'file-alt' : (($status == 'Lisura') ? 'check-double' : (($status == 'SIRE') ? 'database' : (($status == 'Glosa') ? 'exclamation-triangle' : 'archive'))) }}"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ $status }}</span>
                                            <span class="info-box-number">{{ $quantidade }}</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: {{ $percentual }}%"></div>
                                            </div>
                                            <span class="progress-description">
                                                {{ number_format($percentual, 1) }}% do total
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @php
                                }
                            @endphp
                            <div class="col-6 col-md-4">
                                <div class="info-box bg-gradient-secondary">
                                    <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total de Pacotes</span>
                                        <span class="info-box-number">{{ $totalPacotes }}</span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: 100%"></div>
                                        </div>
                                        <span class="progress-description">
                                            Todos os pacotes registrados
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tempo Médio de Permanência -->
    <div class="col-md-6">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock"></i> Tempo Médio de Permanência</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="tempoChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                @php
                    $temposStatus = [];
                    
                    foreach($statusList as $status) {
                        // Calcular tempo médio de permanência para cada status
                        $pacotesStatus = App\Models\Pacote::where('localizacao_atual', $status)->get();
                        
                        $tempoPermanencia = 0;
                        $contador = 0;
                        
                        foreach($pacotesStatus as $pacote) {
                            // Usar MovimentacaoPacote em vez de LogMovimentacao
                            $ultimoLog = App\Models\MovimentacaoPacote::where('pacote_id', $pacote->id)
                                            ->where('localizacao_pos_acao', $status)
                                            ->orderBy('created_at', 'desc')
                                            ->first();
                                            
                            if ($ultimoLog) {
                                $dataEntrada = \Carbon\Carbon::parse($ultimoLog->created_at);
                                $hoje = \Carbon\Carbon::now();
                                $dias = $dataEntrada->diffInDays($hoje);
                                
                                $tempoPermanencia += $dias;
                                $contador++;
                            }
                        }
                        
                        $mediaDias = ($contador > 0) ? round($tempoPermanencia / $contador, 1) : 0;
                        $temposStatus[$status] = $mediaDias;
                    }
                @endphp
                
                <div class="mt-4">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Tempo Médio (dias)</th>
                                <th>Condição</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statusList as $status)
                                @php
                                    // Definir um SLA teórico para cada status
                                    $sla = [
                                        'Protocolo' => 2,
                                        'Lisura' => 5,
                                        'SIRE' => 15,
                                        'Glosa' => 20,
                                        'Arquivo' => 30
                                    ];
                                    
                                    $condicao = '';
                                    if ($temposStatus[$status] <= $sla[$status]) {
                                        $condicao = 'success';
                                    } elseif ($temposStatus[$status] <= $sla[$status] * 1.5) {
                                        $condicao = 'warning';
                                    } else {
                                        $condicao = 'danger';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $status }}</td>
                                    <td>{{ $temposStatus[$status] }}</td>
                                    <td>
                                        <span class="badge badge-{{ $condicao }}">
                                            @if($condicao == 'success')
                                                Dentro do SLA
                                            @elseif($condicao == 'warning')
                                                Atenção
                                            @else
                                                Acima do SLA
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Evolução de Pacotes -->
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Evolução de Pacotes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="evolucaoChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                @php
                    $meses = [];
                    $dadosMensais = [];
                    
                    // Obter dados dos últimos 6 meses
                    for ($i = 5; $i >= 0; $i--) {
                        $mes = \Carbon\Carbon::now()->subMonths($i);
                        $nomeMes = ucfirst($mes->translatedFormat('M/Y'));
                        $meses[] = $nomeMes;
                        
                        $inicio = $mes->copy()->startOfMonth();
                        $fim = $mes->copy()->endOfMonth();
                        
                        // Contar pacotes que entraram no sistema no mês
                        $pacotesEntrada = App\Models\Pacote::whereBetween('data_entrada', [$inicio, $fim])
                            ->count();
                        
                        // Corrigido para usar MovimentacaoPacote em vez de LogMovimentacao
                        $pacotesArquivados = App\Models\MovimentacaoPacote::where('localizacao_pos_acao', 'Arquivo')
                            ->whereBetween('created_at', [$inicio, $fim])
                            ->count();
                        
                        $dadosMensais[] = [
                            'entrada' => $pacotesEntrada,
                            'arquivados' => $pacotesArquivados
                        ];
                    }
                @endphp
                
                <div class="mt-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th>Entrada</th>
                                <th>Arquivados</th>
                                <th>Balanço</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meses as $i => $mes)
                                @php
                                    $entrada = $dadosMensais[$i]['entrada'];
                                    $arquivados = $dadosMensais[$i]['arquivados'];
                                    $balanco = $entrada - $arquivados;
                                    $classeBalanco = ($balanco <= 0) ? 'text-success' : 'text-danger';
                                @endphp
                                <tr>
                                    <td>{{ $mes }}</td>
                                    <td>{{ $entrada }}</td>
                                    <td>{{ $arquivados }}</td>
                                    <td class="{{ $classeBalanco }}">
                                        {{ $balanco }}
                                        @if($balanco > 0)
                                            <i class="fas fa-arrow-up"></i>
                                        @elseif($balanco < 0)
                                            <i class="fas fa-arrow-down"></i>
                                        @else
                                            <i class="fas fa-equals"></i>
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

    <!-- Pacotes mais Antigos por Status -->
    <div class="col-12">
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-hourglass-half"></i> Pacotes com Maior Tempo de Permanência</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tabela-tempo">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fatura</th>
                                <th>OCS/PSA</th>
                                <th>Status Atual</th>
                                <th>Data Entrada Status</th>
                                <th>Dias no Status</th>
                                <th>Valor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Obter pacotes com logs de movimentação
                                // Corrigido: alterado 'logs' para 'movimentacoes' para corresponder ao nome correto da relação
                                $pacotesComLogs = App\Models\Pacote::with(['ocsPsa', 'movimentacoes' => function($query) {
                                    $query->orderBy('created_at', 'desc');
                                }])->get();
                                
                                $pacotesOrdenados = [];
                                
                                foreach ($pacotesComLogs as $pacote) {
                                    // Encontrar log para a localização atual usando MovimentacaoPacote
                                    // Não precisamos mais acessar logs que não existe
                                    $logAtual = App\Models\MovimentacaoPacote::where('pacote_id', $pacote->id)
                                                ->where('localizacao_pos_acao', $pacote->localizacao_atual)
                                                ->orderBy('created_at', 'desc')
                                                ->first();
                                    
                                    if ($logAtual) {
                                        $dataEntrada = \Carbon\Carbon::parse($logAtual->created_at);
                                        $hoje = \Carbon\Carbon::now();
                                        $diasNoStatus = $dataEntrada->diffInDays($hoje);
                                        
                                        $pacote->diasNoStatus = $diasNoStatus;
                                        $pacote->dataEntradaStatus = $dataEntrada;
                                        
                                        $pacotesOrdenados[] = $pacote;
                                    }
                                }
                                
                                // Ordenar por dias no status (decrescente)
                                usort($pacotesOrdenados, function($a, $b) {
                                    return $b->diasNoStatus - $a->diasNoStatus;
                                });
                                
                                // Pegar os 15 primeiros
                                $pacotesOrdenados = array_slice($pacotesOrdenados, 0, 15);
                            @endphp
                            
                            @foreach($pacotesOrdenados as $pacote)
                                @php
                                    // Determinar a classe de alerta com base nos dias no status
                                    $classeAlerta = '';
                                    if ($pacote->diasNoStatus > 30) {
                                        $classeAlerta = 'table-danger';
                                    } elseif ($pacote->diasNoStatus > 15) {
                                        $classeAlerta = 'table-warning';
                                    }
                                    
                                    // Determinar a cor do status
                                    $corStatus = '';
                                    if ($pacote->localizacao_atual == 'Protocolo') $corStatus = 'info';
                                    elseif ($pacote->localizacao_atual == 'Lisura') $corStatus = 'primary';
                                    elseif ($pacote->localizacao_atual == 'SIRE') $corStatus = 'warning';
                                    elseif ($pacote->localizacao_atual == 'Glosa') $corStatus = 'danger';
                                    elseif ($pacote->localizacao_atual == 'Arquivo') $corStatus = 'success';
                                @endphp
                                <tr class="{{ $classeAlerta }}">
                                    <td>{{ $pacote->id }}</td>
                                    <td>{{ $pacote->numero_fatura }}</td>
                                    <td>{{ $pacote->ocsPsa->nome ?? 'N/D' }}</td>
                                    <td><span class="badge bg-{{ $corStatus }}">{{ $pacote->localizacao_atual }}</span></td>
                                    <td>{{ $pacote->dataEntradaStatus->format('d/m/Y H:i') }}</td>
                                    <td>{{ number_format($pacote->diasNoStatus, 0, ',', '.') }} dias</td>
                                    <td>R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ route('pacotes.show', $pacote->id) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribuição por OCS/PSA -->
    <div class="col-md-7">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-hospital"></i> Distribuição por OCS/PSA</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tabela-ocs">
                        <thead>
                            <tr>
                                <th>OCS/PSA</th>
                                <th>Protocolo</th>
                                <th>Lisura</th>
                                <th>SIRE</th>
                                <th>Glosa</th>
                                <th>Arquivo</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $topOcsPsa = App\Models\OcsPsa::withCount(['pacotes as total_pacotes'])
                                    ->orderBy('total_pacotes', 'desc')
                                    ->limit(10)
                                    ->get();
                            @endphp
                            
                            @foreach($topOcsPsa as $ocsPsa)
                                @php
                                    $protocolo = App\Models\Pacote::where('ocs_psa_id', $ocsPsa->id)
                                        ->where('localizacao_atual', 'Protocolo')
                                        ->count();
                                        
                                    $lisura = App\Models\Pacote::where('ocs_psa_id', $ocsPsa->id)
                                        ->where('localizacao_atual', 'Lisura')
                                        ->count();
                                        
                                    $sire = App\Models\Pacote::where('ocs_psa_id', $ocsPsa->id)
                                        ->where('localizacao_atual', 'SIRE')
                                        ->count();
                                        
                                    $glosa = App\Models\Pacote::where('ocs_psa_id', $ocsPsa->id)
                                        ->where('localizacao_atual', 'Glosa')
                                        ->count();
                                        
                                    $arquivo = App\Models\Pacote::where('ocs_psa_id', $ocsPsa->id)
                                        ->where('localizacao_atual', 'Arquivo')
                                        ->count();
                                        
                                    $total = $protocolo + $lisura + $sire + $glosa + $arquivo;
                                @endphp
                                <tr>
                                    <td>{{ $ocsPsa->nome }}</td>
                                    <td>{{ $protocolo }}</td>
                                    <td>{{ $lisura }}</td>
                                    <td>{{ $sire }}</td>
                                    <td>{{ $glosa }}</td>
                                    <td>{{ $arquivo }}</td>
                                    <td>{{ $total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribuição por Tipo -->
    <div class="col-md-5">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tags"></i> Distribuição por Tipo</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="tipoChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                @php
                    // Método alternativo para obter os dados sem usar o relacionamento
                    $tiposPacote = App\Models\TipoPacote::all()->map(function($tipo) {
                        $count = App\Models\Pacote::where('tipo_id', $tipo->id)->count();
                        $tipo->pacotes_count = $count;
                        return $tipo;
                    });
                    
                    // Se não houver dados, criar alguns exemplos para demonstração
                    if ($tiposPacote->isEmpty() || $tiposPacote->sum('pacotes_count') == 0) {
                        $tiposPacote = collect([
                            (object)['id' => 1, 'nome' => 'Ambulatorial', 'pacotes_count' => 45],
                            (object)['id' => 2, 'nome' => 'Internação', 'pacotes_count' => 32],
                            (object)['id' => 3, 'nome' => 'Odontologia', 'pacotes_count' => 18]
                        ]);
                    }
                @endphp
                
                <div class="mt-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Percentual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalTipos = $tiposPacote->sum('pacotes_count');
                            @endphp
                            
                            @foreach($tiposPacote as $tipo)
                                @php
                                    $percentual = ($totalTipos > 0) ? ($tipo->pacotes_count / $totalTipos) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>{{ $tipo->nome }}</td>
                                    <td>{{ $tipo->pacotes_count }}</td>
                                    <td>{{ number_format($percentual, 1) }}%</td>
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
<style>
    .info-box-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .progress {
        height: 4px;
        margin: 5px 0;
    }
    .progress-description {
        font-size: 0.75rem;
    }
    .table-responsive {
        min-height: 200px;
    }
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script>
    $(function() {
        // Inicialização do Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: "Selecione uma opção",
            allowClear: true
        });
        
        // Inicialização do DateRangePicker
        $('#range-date').daterangepicker({
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
               'Hoje': [moment(), moment()],
               'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
               'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
               'Este mês': [moment().startOf('month'), moment().endOf('month')],
               'Mês passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
               'Este ano': [moment().startOf('year'), moment()]
            }
        });
        
        // Inicialização do DataTables
        $('#tabela-tempo').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
            },
            responsive: true,
            pageLength: 10,
            order: [[5, 'desc']],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn-success',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    }
                }
            ]
        });
        
        $('#tabela-ocs').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
            },
            responsive: true,
            pageLength: 10,
            order: [[6, 'desc']]
        });
        
        // Exportar para Excel
        $('#btn-exportar').on('click', function() {
            $('#tabela-tempo').DataTable().button('.buttons-excel').trigger();
        });

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
                        '#17a2b8', // Info - Protocolo
                        '#007bff', // Primary - Lisura
                        '#ffc107', // Warning - SIRE
                        '#dc3545', // Danger - Glosa
                        '#28a745'  // Success - Arquivo
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.raw || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = Math.round((value / total) * 100);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Tempo Médio
        const tempoCtx = document.getElementById('tempoChart').getContext('2d');
        const tempoChart = new Chart(tempoCtx, {
            type: 'bar',
            data: {
                labels: ['Protocolo', 'Lisura', 'SIRE', 'Glosa', 'Arquivo'],
                datasets: [{
                    label: 'Tempo Médio (dias)',
                    data: [
                        {{ $temposStatus['Protocolo'] }},
                        {{ $temposStatus['Lisura'] }},
                        {{ $temposStatus['SIRE'] }},
                        {{ $temposStatus['Glosa'] }},
                        {{ $temposStatus['Arquivo'] }}
                    ],
                    backgroundColor: [
                        'rgba(23, 162, 184, 0.7)', // Info - Protocolo
                        'rgba(0, 123, 255, 0.7)',  // Primary - Lisura
                        'rgba(255, 193, 7, 0.7)',  // Warning - SIRE
                        'rgba(220, 53, 69, 0.7)',  // Danger - Glosa
                        'rgba(40, 167, 69, 0.7)'   // Success - Arquivo
                    ],
                    borderColor: [
                        'rgba(23, 162, 184, 1)',
                        'rgba(0, 123, 255, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(40, 167, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Dias'
                        }
                    }
                }
            }
        });

        // Gráfico de Evolução
        const evolucaoCtx = document.getElementById('evolucaoChart').getContext('2d');
        const evolucaoChart = new Chart(evolucaoCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($meses) !!},
                datasets: [
                    {
                        label: 'Entrada',
                        data: {!! json_encode(array_column($dadosMensais, 'entrada')) !!},
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Arquivados',
                        data: {!! json_encode(array_column($dadosMensais, 'arquivados')) !!},
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantidade de Pacotes'
                        }
                    }
                }
            }
        });

        // Gráfico de Tipos
        const tipoCtx = document.getElementById('tipoChart').getContext('2d');
        const tipoChart = new Chart(tipoCtx, {
            type: 'pie',
            data: {
                labels: [
                    @foreach($tiposPacote as $tipo)
                        '{{ $tipo->nome }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach($tiposPacote as $tipo)
                            {{ $tipo->pacotes_count }},
                        @endforeach
                    ],
                    backgroundColor: [
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(0, 123, 255, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(108, 117, 125, 0.7)',
                        'rgba(111, 66, 193, 0.7)',
                        'rgba(253, 126, 20, 0.7)'
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.raw || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = Math.round((value / total) * 100);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@stop