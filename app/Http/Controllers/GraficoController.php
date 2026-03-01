<?php

namespace App\Http\Controllers;

use App\Models\Glosa;
use App\Models\MotivoGlosa;
use App\Models\Movimentacao;
use App\Models\OcsPsa;
use App\Models\Pacote;
use App\Models\Configuracao;
use App\Models\TipoConta;
use App\Models\TipoPacote;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GraficoController extends Controller
{
    public function index()
    {
        // Carrega dados para os filtros
        $ocsPsas = OcsPsa::orderBy('nome')->get();
        $tiposPacote = TipoPacote::orderBy('nome')->get();
        $tiposContas = TipoConta::orderBy('nome')->get();
        
        $estadosGlosa = [
            'todos' => 'Todos',
            'pendente' => 'Pendente', 
            'em_recurso' => 'Em Recurso',
            'finalizada' => 'Finalizada',
            'irrecuperavel' => 'Irrecuperável'
        ];
        
        return view('graficos.index', compact('ocsPsas', 'tiposPacote', 'tiposContas', 'estadosGlosa'));
    }
    
    public function kpis(Request $request)
    {
        try {
            // Construir a query base
            $query = Pacote::query();
            
            // Aplicar filtros
            if ($request->filled('periodo')) {
                [$dataInicio, $dataFim] = explode(' - ', $request->periodo);
                $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicio)->startOfDay();
                $dataFim = Carbon::createFromFormat('d/m/Y', $dataFim)->endOfDay();
                $query->whereBetween('data_entrada', [$dataInicio, $dataFim]);
            }
            
            if ($request->filled('ocs_psa_id') && $request->ocs_psa_id !== 'todos') {
                $query->where('ocs_psa_id', $request->ocs_psa_id);
            }
            
            if ($request->filled('tipo_id') && $request->tipo_id !== 'todos') {
                $query->where('tipo_id', $request->tipo_id);
            }
            
            if ($request->filled('tipo_conta_id') && $request->tipo_conta_id !== 'todos') {
                $query->where('tipo_conta_id', $request->tipo_conta_id);
            }
            
            if ($request->filled('estado_glosa') && $request->estado_glosa !== 'todos') {
                $query->where('estado_glosa', $request->estado_glosa);
            }
            
            // Calcular KPIs
            $totalPacotes = $query->count();
            $valorTotalFaturas = $query->sum('valor_fatura');
            
            // Taxa média de glosa
            $valorTotalGlosa = $query->sum('valor_glosa');
            $taxaMediaGlosa = $valorTotalFaturas > 0 ? ($valorTotalGlosa / $valorTotalFaturas) * 100 : 0;
            
            // Tempo médio em dias
            $tempoMedioDias = DB::table('pacotes')
                ->selectRaw('AVG(DATEDIFF(IFNULL(updated_at, NOW()), data_entrada)) as tempo_medio')
                ->whereNotNull('data_entrada')
                ->first()->tempo_medio ?? 0;
            
            return response()->json([
                'total_pacotes' => $totalPacotes,
                'valor_total_faturas' => $valorTotalFaturas,
                'taxa_media_glosa' => round($taxaMediaGlosa, 1),
                'tempo_medio_dias' => round($tempoMedioDias, 1)
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao carregar KPIs: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function fluxo(Request $request)
    {
        try {
            // Aplicar filtros
            $query = Pacote::query();
            
            if ($request->filled('periodo')) {
                [$dataInicio, $dataFim] = explode(' - ', $request->periodo);
                $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicio)->startOfDay();
                $dataFim = Carbon::createFromFormat('d/m/Y', $dataFim)->endOfDay();
                $query->whereBetween('data_entrada', [$dataInicio, $dataFim]);
            }
            
            if ($request->filled('ocs_psa_id') && $request->ocs_psa_id !== 'todos') {
                $query->where('ocs_psa_id', $request->ocs_psa_id);
            }
            
            if ($request->filled('tipo_id') && $request->tipo_id !== 'todos') {
                $query->where('tipo_id', $request->tipo_id);
            }
            
            if ($request->filled('tipo_conta_id') && $request->tipo_conta_id !== 'todos') {
                $query->where('tipo_conta_id', $request->tipo_conta_id);
            }
            
            if ($request->filled('estado_glosa') && $request->estado_glosa !== 'todos') {
                $query->where('estado_glosa', $request->estado_glosa);
            }
            
            // Dados de quantidade por etapa
            $protocolo = $query->clone()->where('localizacao_atual', 'Protocolo')->count();
            $lisura = $query->clone()->where('localizacao_atual', 'Lisura')->count();
            $sire = $query->clone()->where('localizacao_atual', 'SIRE')->count();
            $glosa = $query->clone()->where('localizacao_atual', 'Glosa')->count();
            $arquivo = $query->clone()->where('localizacao_atual', 'Arquivo')->count();
            
            // Tempo médio por etapa (em dias)
            $tempoEtapas = [
                'labels' => ['Protocolo', 'Lisura', 'SIRE', 'Glosa', 'Arquivo'],
                'values' => []
            ];
            
            // Volume por etapa
            $volumeEtapas = [
                'labels' => ['Protocolo', 'Lisura', 'SIRE', 'Glosa', 'Arquivo'],
                'values' => [$protocolo, $lisura, $sire, $glosa, $arquivo]
            ];
            
            // Calcular tempo médio por etapa usando movimentações
            $etapas = ['Protocolo', 'Lisura', 'SIRE', 'Glosa', 'Arquivo'];
            foreach ($etapas as $etapa) {
                // Buscar tempo médio de permanência em cada etapa
                $tempoMedio = DB::table('movimentacoes_pacote as m1')
                    ->join('movimentacoes_pacote as m2', function ($join) {
                        $join->on('m1.pacote_id', '=', 'm2.pacote_id')
                            ->whereRaw('m2.id = (select min(id) from movimentacoes_pacote where pacote_id = m1.pacote_id and id > m1.id)');
                    })
                    ->where('m1.localizacao_pos_acao', $etapa)
                    ->selectRaw('AVG(DATEDIFF(m2.created_at, m1.created_at)) as tempo_medio')
                    ->first()->tempo_medio ?? 0;
                
                $tempoEtapas['values'][] = round($tempoMedio, 1);
            }
            
            // Identificar possíveis gargalos (pacotes com tempo acima da média em cada etapa)
            $gargalos = [];
            
            // Verificar pacotes com tempo acima da média em Glosa
            $pacotesAtrasadosGlosa = DB::table('movimentacoes_pacote as m1')
                ->join('movimentacoes_pacote as m2', function ($join) {
                    $join->on('m1.pacote_id', '=', 'm2.pacote_id')
                        ->whereRaw('m2.id = (select min(id) from movimentacoes_pacote where pacote_id = m1.pacote_id and id > m1.id)');
                })
                ->join('pacotes', 'pacotes.id', '=', 'm1.pacote_id')
                ->where('m1.localizacao_pos_acao', 'Glosa')
                ->whereRaw('DATEDIFF(m2.created_at, m1.created_at) > ?', [$tempoEtapas['values'][3] * 1.5])
                ->count();
            
            if ($pacotesAtrasadosGlosa > 0) {
                $gargalos[] = [
                    'etapa' => 'Glosa', 
                    'problema' => 'Prazo excedido', 
                    'quantidade' => $pacotesAtrasadosGlosa, 
                    'impacto' => round(($pacotesAtrasadosGlosa / max(1, $glosa)) * 100)
                ];
            }
            
            return response()->json([
                'protocolo' => $protocolo,
                'lisura' => $lisura,
                'sire' => $sire,
                'glosa' => $glosa,
                'arquivo' => $arquivo,
                'tempo_etapas' => $tempoEtapas,
                'volume_etapas' => $volumeEtapas,
                'gargalos' => $gargalos
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao carregar dados de fluxo: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function status(Request $request)
    {
        try {
            // Aplicar filtros
            $query = Pacote::query();
            
            if ($request->filled('periodo')) {
                [$dataInicio, $dataFim] = explode(' - ', $request->periodo);
                $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicio)->startOfDay();
                $dataFim = Carbon::createFromFormat('d/m/Y', $dataFim)->endOfDay();
                $query->whereBetween('data_entrada', [$dataInicio, $dataFim]);
            }
            
            if ($request->filled('ocs_psa_id') && $request->ocs_psa_id !== 'todos') {
                $query->where('ocs_psa_id', $request->ocs_psa_id);
            }
            
            if ($request->filled('tipo_id') && $request->tipo_id !== 'todos') {
                $query->where('tipo_id', $request->tipo_id);
            }
            
            if ($request->filled('tipo_conta_id') && $request->tipo_conta_id !== 'todos') {
                $query->where('tipo_conta_id', $request->tipo_conta_id);
            }
            
            if ($request->filled('estado_glosa') && $request->estado_glosa !== 'todos') {
                $query->where('estado_glosa', $request->estado_glosa);
            }
            
            // Buscar dados agrupados por localização atual
            $status = DB::table('pacotes')
                ->select('localizacao_atual as status', DB::raw('count(*) as total'))
                ->whereIn('id', $query->select('id'))
                ->groupBy('localizacao_atual')
                ->orderBy('total', 'desc')
                ->get();
            
            // Preparar dados para o gráfico
            $labels = [];
            $values = [];
            
            // Definir cores específicas para cada status
            $coresPorStatus = [
                'protocolo' => '#3498db',   // azul
                'lisura' => '#2ecc71',      // verde
                'sire' => '#f39c12',        // laranja
                'glosa' => '#e74c3c',       // vermelho
                'arquivo' => '#9b59b6',     // roxo
                'arquivado' => '#1abc9c'    // verde-água
            ];
            
            $colors = []; // Array para armazenar as cores que serão retornadas
            
            foreach ($status as $item) {
                $labels[] = $item->status;
                $values[] = $item->total;
                
                // Determinar a cor para este status
                $statusLowerCase = strtolower($item->status);
                $corEncontrada = false;
                
                // Verificar correspondências diretas ou parciais
                foreach ($coresPorStatus as $key => $color) {
                    if ($statusLowerCase === $key || strpos($statusLowerCase, $key) !== false) {
                        $colors[] = $color;
                        $corEncontrada = true;
                        break;
                    }
                }
                
                // Se não encontrou cor específica, usar uma cor padrão
                if (!$corEncontrada) {
                    $colors[] = '#95a5a6'; // cinza como cor padrão
                }
            }
            
            return response()->json([
                'labels' => $labels,
                'values' => $values,
                'colors' => $colors // Importante: retornar as cores definidas!
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao carregar dados de status: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function tendencia(Request $request)
    {
        try {
            // Buscar dados dos últimos 6 meses
            $dataFinal = Carbon::now()->endOfMonth();
            $dataInicial = Carbon::now()->subMonths(5)->startOfMonth();
            
            // Solução - usar iterator_to_array para converter o Generator em array:
            $period = CarbonPeriod::create($dataInicial, '1 month', $dataFinal);
            $meses = [];
            foreach ($period as $data) {
                $meses[] = $data->format('Y-m');
            }
            
            // Aplicar filtros
            $queryBase = Pacote::query();
            
            if ($request->filled('ocs_psa_id') && $request->ocs_psa_id !== 'todos') {
                $queryBase->where('ocs_psa_id', $request->ocs_psa_id);
            }
            
            if ($request->filled('tipo_id') && $request->tipo_id !== 'todos') {
                $queryBase->where('tipo_id', $request->tipo_id);
            }
            
            if ($request->filled('tipo_conta_id') && $request->tipo_conta_id !== 'todos') {
                $queryBase->where('tipo_conta_id', $request->tipo_conta_id);
            }
            
            if ($request->filled('estado_glosa') && $request->estado_glosa !== 'todos') {
                $queryBase->where('estado_glosa', $request->estado_glosa);
            }
            
            // Entradas por mês (pela data_entrada)
            $entradas = [];
            // Saídas por mês (movido para Arquivado)
            $saidas = [];
            
            foreach ($meses as $mes) {
                $dataInicioMes = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
                $dataFimMes = Carbon::createFromFormat('Y-m', $mes)->endOfMonth();
                
                // Contar entradas no mês
                $queryEntradas = clone $queryBase;
                $entradas[] = $queryEntradas->whereBetween('data_entrada', [$dataInicioMes, $dataFimMes])->count();
                
                // Contar saídas/arquivamentos no mês (usando a tabela movimentacoes_pacote)
                $saidas[] = DB::table('movimentacoes_pacote')
                    ->join('pacotes', 'pacotes.id', '=', 'movimentacoes_pacote.pacote_id')
                    ->where('movimentacoes_pacote.localizacao_pos_acao', 'Arquivo')
                    ->whereBetween('movimentacoes_pacote.created_at', [$dataInicioMes, $dataFimMes])
                    ->whereIn('pacotes.id', function($query) use ($queryBase) {
                        $query->select('id')->from('pacotes')->whereIn('id', $queryBase->select('id'));
                    })
                    ->distinct('pacote_id')
                    ->count('pacote_id');
            }
            
            // Formatar os labels dos meses
            $labels = array_map(function($mes) {
                return Carbon::createFromFormat('Y-m', $mes)->format('M/Y');
            }, $meses);
            
            return response()->json([
                'labels' => $labels,
                'entradas' => $entradas,
                'saidas' => $saidas
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao carregar dados de tendência: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function volume(Request $request)
    {
        try {
            // Aplicar filtros
            $query = Pacote::query();
            
            if ($request->filled('periodo')) {
                [$dataInicio, $dataFim] = explode(' - ', $request->periodo);
                $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicio)->startOfDay();
                $dataFim = Carbon::createFromFormat('d/m/Y', $dataFim)->endOfDay();
                $query->whereBetween('data_entrada', [$dataInicio, $dataFim]);
            }
            
            if ($request->filled('tipo_id') && $request->tipo_id !== 'todos') {
                $query->where('tipo_id', $request->tipo_id);
            }
            
            if ($request->filled('tipo_conta_id') && $request->tipo_conta_id !== 'todos') {
                $query->where('tipo_conta_id', $request->tipo_conta_id);
            }
            
            if ($request->filled('estado_glosa') && $request->estado_glosa !== 'todos') {
                $query->where('estado_glosa', $request->estado_glosa);
            }
            
            // Buscar top 10 OCS/PSA por volume de pacotes
            $topOcsPsa = DB::table('pacotes')
                ->join('ocs_psa', 'ocs_psa.id', '=', 'pacotes.ocs_psa_id')
                ->select('ocs_psa.nome', DB::raw('count(*) as total'))
                ->whereIn('pacotes.id', $query->select('id'))
                ->groupBy('ocs_psa.nome')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();
            
            $labels = $topOcsPsa->pluck('nome')->toArray();
            $values = $topOcsPsa->pluck('total')->toArray();
            
            return response()->json([
                'labels' => $labels,
                'values' => $values
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao carregar dados de volume: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function tipo(Request $request)
    {
        try {
            // Aplicar filtros
            $query = Pacote::query();
            
            if ($request->filled('periodo')) {
                [$dataInicio, $dataFim] = explode(' - ', $request->periodo);
                $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicio)->startOfDay();
                $dataFim = Carbon::createFromFormat('d/m/Y', $dataFim)->endOfDay();
                $query->whereBetween('data_entrada', [$dataInicio, $dataFim]);
            }
            
            if ($request->filled('ocs_psa_id') && $request->ocs_psa_id !== 'todos') {
                $query->where('ocs_psa_id', $request->ocs_psa_id);
            }
            
            if ($request->filled('tipo_conta_id') && $request->tipo_conta_id !== 'todos') {
                $query->where('tipo_conta_id', $request->tipo_conta_id);
            }
            
            if ($request->filled('estado_glosa') && $request->estado_glosa !== 'todos') {
                $query->where('estado_glosa', $request->estado_glosa);
            }
            
            // Buscar distribuição por tipo de pacote
            $tiposPacote = DB::table('pacotes')
                ->join('tipos_pacote', 'tipos_pacote.id', '=', 'pacotes.tipo_id')
                ->select('tipos_pacote.nome', DB::raw('count(*) as total'))
                ->whereIn('pacotes.id', $query->select('id'))
                ->groupBy('tipos_pacote.nome')
                ->orderBy('total', 'desc')
                ->get();
            
            $labels = $tiposPacote->pluck('nome')->toArray();
            $values = $tiposPacote->pluck('total')->toArray();
            
            return response()->json([
                'labels' => $labels,
                'values' => $values
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao carregar dados de tipo: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function financeiro(Request $request)
    {
        try {
            // Construir a query base
            $query = Pacote::query();
            
            // Aplicar filtros
            if ($request->filled('periodo')) {
                [$dataInicio, $dataFim] = explode(' - ', $request->periodo);
                $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicio)->startOfDay();
                $dataFim = Carbon::createFromFormat('d/m/Y', $dataFim)->endOfDay();
                $query->whereBetween('data_entrada', [$dataInicio, $dataFim]);
            }
            
            if ($request->filled('ocs_psa_id') && $request->ocs_psa_id !== 'todos') {
                $query->where('ocs_psa_id', $request->ocs_psa_id);
            }
            
            if ($request->filled('tipo_id') && $request->tipo_id !== 'todos') {
                $query->where('tipo_id', $request->tipo_id);
            }
            
            if ($request->filled('tipo_conta_id') && $request->tipo_conta_id !== 'todos') {
                $query->where('tipo_conta_id', $request->tipo_conta_id);
            }
            
            if ($request->filled('estado_glosa') && $request->estado_glosa !== 'todos') {
                $query->where('estado_glosa', $request->estado_glosa);
            }
            
            // Calcular KPIs financeiros
            $valorImplantado = $query->sum('valor_pago');
            $valorPendente = $query->sum('valor_pendente');
            $valorRecursado = $query->sum('valor_recurso_glosa');
            $valorGlosadoFinal = $query->sum('valor_glosa') - $query->sum('valor_deferido');
            
            // Preparar dados para composição do valor total
            $composicao = [
                'labels' => ['Implantado', 'Pendente', 'Recursado', 'Glosado Final'],
                'values' => [$valorImplantado, $valorPendente, $valorRecursado, $valorGlosadoFinal],
                'colors' => ['#2ecc71', '#3498db', '#f39c12', '#e74c3c']
            ];
            
            // Preparar dados para evolução dos valores mensais
            $evolucao = [
                'labels' => [],
                'faturado' => [],
                'implantado' => [],
                'glosado' => []
            ];
            
            // Últimos 6 meses
            for ($i = 5; $i >= 0; $i--) {
                $mesInicio = Carbon::now()->subMonths($i)->startOfMonth();
                $mesFim = Carbon::now()->subMonths($i)->endOfMonth();
                
                $evolucao['labels'][] = $mesInicio->format('M/Y');
                
                // Valor faturado no mês
                $valorFaturadoMes = $query->clone()
                    ->whereBetween('data_entrada', [$mesInicio, $mesFim])
                    ->sum('valor_fatura');
                    
                // Valor implantado no mês
                $valorImplantadoMes = $query->clone()
                    ->whereBetween('data_entrada', [$mesInicio, $mesFim])
                    ->sum('valor_pago');
                    
                // Valor glosado no mês
                $valorGlosadoMes = $query->clone()
                    ->whereBetween('data_entrada', [$mesInicio, $mesFim])
                    ->sum('valor_glosa');
                
                $evolucao['faturado'][] = $valorFaturadoMes;
                $evolucao['implantado'][] = $valorImplantadoMes;
                $evolucao['glosado'][] = $valorGlosadoMes;
            }
            
            // Top 5 OCS/PSA por valor
            $topOcsPsa = DB::table('pacotes')
                ->join('ocs_psa', 'ocs_psa.id', '=', 'pacotes.ocs_psa_id')
                ->select('ocs_psa.nome', DB::raw('SUM(pacotes.valor_fatura) as valor_total'))
                ->groupBy('ocs_psa.nome')
                ->orderBy('valor_total', 'desc')
                ->limit(5)
                ->get();
            
            $topOcsPsaData = [
                'labels' => $topOcsPsa->pluck('nome')->toArray(),
                'values' => $topOcsPsa->pluck('valor_total')->toArray()
            ];
            
            // Distribuição por tipo de conta
            $tipoConta = DB::table('pacotes')
                ->join('tipos_conta', 'tipos_conta.id', '=', 'pacotes.tipo_conta_id')
                ->select('tipos_conta.nome', DB::raw('SUM(pacotes.valor_fatura) as valor_total'))
                ->groupBy('tipos_conta.nome')
                ->orderBy('valor_total', 'desc')
                ->get();
            
            $tipoContaData = [
                'labels' => $tipoConta->pluck('nome')->toArray(),
                'values' => $tipoConta->pluck('valor_total')->toArray()
            ];
            
            return response()->json([
                'kpis' => [
                    'valor_implantado' => $valorImplantado,
                    'valor_pendente' => $valorPendente,
                    'valor_recursado' => $valorRecursado,
                    'valor_glosado_final' => $valorGlosadoFinal
                ],
                'composicao' => $composicao,
                'evolucao' => $evolucao,
                'top_ocspsa' => $topOcsPsaData,
                'tipo_conta' => $tipoContaData
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao carregar dados financeiros: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function glosas(Request $request)
    {
        try {
            // Construir a query base
            $query = Pacote::query();
            
            // Aplicar filtros
            if ($request->filled('periodo')) {
                [$dataInicio, $dataFim] = explode(' - ', $request->periodo);
                $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicio)->startOfDay();
                $dataFim = Carbon::createFromFormat('d/m/Y', $dataFim)->endOfDay();
                $query->whereBetween('data_entrada', [$dataInicio, $dataFim]);
            }
            
            if ($request->filled('ocs_psa_id') && $request->ocs_psa_id !== 'todos') {
                $query->where('ocs_psa_id', $request->ocs_psa_id);
            }
            
            if ($request->filled('tipo_id') && $request->tipo_id !== 'todos') {
                $query->where('tipo_id', $request->tipo_id);
            }
            
            if ($request->filled('tipo_conta_id') && $request->tipo_conta_id !== 'todos') {
                $query->where('tipo_conta_id', $request->tipo_conta_id);
            }
            
            if ($request->filled('estado_glosa') && $request->estado_glosa !== 'todos') {
                $query->where('estado_glosa', $request->estado_glosa);
            }
            
            // Calcular KPIs de glosas
            $valorGlosado = $query->sum('valor_glosa');
            $pacotesGlosados = $query->where('valor_glosa', '>', 0)->count();
            
            $valorRecuperado = $query->sum('valor_deferido');
            $taxaRecuperacao = $valorGlosado > 0 ? ($valorRecuperado / $valorGlosado * 100) : 0;
            
            $valorIrrecuperavel = $valorGlosado - $valorRecuperado;
            
            // Motivos de glosa mais frequentes
            $motivosGlosa = DB::table('pacotes')
                ->join('motivos_glosa', 'motivos_glosa.id', '=', 'pacotes.motivo_glosa_id')
                ->select('motivos_glosa.nome', DB::raw('COUNT(*) as total'))
                ->whereNotNull('pacotes.motivo_glosa_id')
                ->whereIn('pacotes.id', $query->select('id'))
                ->groupBy('motivos_glosa.nome')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();
                
            $motivosGlosaData = [
                'labels' => $motivosGlosa->pluck('nome')->toArray(),
                'values' => $motivosGlosa->pluck('total')->toArray()
            ];
            
            // Status dos recursos de glosa
            $statusRecursos = [
                'labels' => ['Não Recursado', 'Em Análise', 'Deferido', 'Indeferido'],
                'values' => [
                    $query->clone()->where('valor_glosa', '>', 0)->where('valor_recurso_glosa', 0)->count(),
                    $query->clone()->where('valor_recurso_glosa', '>', 0)->where('valor_deferido', 0)->count(),
                    $query->clone()->where('valor_deferido', '>', 0)->count(),
                    $query->clone()->where('valor_recurso_glosa', '>', 0)->where('valor_deferido', 0)->where('estado_glosa', 'finalizada')->count()
                ],
                'colors' => ['#95a5a6', '#3498db', '#2ecc71', '#e74c3c']
            ];
            
            // OCS/PSA com maior taxa de glosa
            $ocsTaxaGlosa = DB::table('pacotes')
                ->join('ocs_psa', 'ocs_psa.id', '=', 'pacotes.ocs_psa_id')
                ->select(
                    'ocs_psa.nome',
                    DB::raw('SUM(pacotes.valor_glosa) as valor_glosado'),
                    DB::raw('SUM(pacotes.valor_fatura) as valor_faturado'),
                    DB::raw('(SUM(pacotes.valor_glosa) / SUM(pacotes.valor_fatura)) * 100 as taxa_glosa')
                )
                ->whereIn('pacotes.id', $query->select('id'))
                ->where('pacotes.valor_fatura', '>', 0)
                ->groupBy('ocs_psa.nome')
                ->orderBy('taxa_glosa', 'desc')
                ->limit(5)
                ->get();
                
            $ocsTaxaGlosaData = [
                'labels' => $ocsTaxaGlosa->pluck('nome')->toArray(),
                'values' => $ocsTaxaGlosa->pluck('taxa_glosa')->toArray()
            ];
            
            // Tendência de glosas (últimos 6 meses)
            $tendencia = [
                'labels' => [],
                'taxa_glosa' => [],
                'taxa_recuperacao' => []
            ];
            
            // Últimos 6 meses
            for ($i = 5; $i >= 0; $i--) {
                $mesInicio = Carbon::now()->subMonths($i)->startOfMonth();
                $mesFim = Carbon::now()->subMonths($i)->endOfMonth();
                
                $tendencia['labels'][] = $mesInicio->format('M/Y');
                
                $valorFaturadoMes = $query->clone()
                    ->whereBetween('data_entrada', [$mesInicio, $mesFim])
                    ->sum('valor_fatura');
                    
                $valorGlosadoMes = $query->clone()
                    ->whereBetween('data_entrada', [$mesInicio, $mesFim])
                    ->sum('valor_glosa');
                    
                $valorDeferidoMes = $query->clone()
                    ->whereBetween('data_entrada', [$mesInicio, $mesFim])
                    ->sum('valor_deferido');
                
                $taxaGlosaMes = $valorFaturadoMes > 0 ? ($valorGlosadoMes / $valorFaturadoMes * 100) : 0;
                $taxaRecuperacaoMes = $valorGlosadoMes > 0 ? ($valorDeferidoMes / $valorGlosadoMes * 100) : 0;
                
                $tendencia['taxa_glosa'][] = round($taxaGlosaMes, 2);
                $tendencia['taxa_recuperacao'][] = round($taxaRecuperacaoMes, 2);
            }
            
            return response()->json([
                'kpis' => [
                    'valor_glosado' => $valorGlosado,
                    'pacotes_glosados' => $pacotesGlosados,
                    'taxa_recuperacao' => round($taxaRecuperacao, 2),
                    'valor_irrecuperavel' => $valorIrrecuperavel
                ],
                'motivos_glosa' => $motivosGlosaData,
                'status_recursos' => $statusRecursos,
                'ocs_taxa_glosa' => $ocsTaxaGlosaData,
                'tendencia_glosa' => $tendencia
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao carregar dados de glosas: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function performance(Request $request)
    {
        try {
            // Construir a query base
            $query = Pacote::query();
            
            // Aplicar filtros
            if ($request->filled('periodo')) {
                [$dataInicio, $dataFim] = explode(' - ', $request->periodo);
                $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicio)->startOfDay();
                $dataFim = Carbon::createFromFormat('d/m/Y', $dataFim)->endOfDay();
                $query->whereBetween('data_entrada', [$dataInicio, $dataFim]);
            }
            
            if ($request->filled('ocs_psa_id') && $request->ocs_psa_id !== 'todos') {
                $query->where('ocs_psa_id', $request->ocs_psa_id);
            }
            
            if ($request->filled('tipo_id') && $request->tipo_id !== 'todos') {
                $query->where('tipo_id', $request->tipo_id);
            }
            
            if ($request->filled('tipo_conta_id') && $request->tipo_conta_id !== 'todos') {
                $query->where('tipo_conta_id', $request->tipo_conta_id);
            }
            
            if ($request->filled('estado_glosa') && $request->estado_glosa !== 'todos') {
                $query->where('estado_glosa', $request->estado_glosa);
            }
            
            // Calcular KPIs de performance
            // Tempo médio total em dias
            $tempoMedio = DB::table('pacotes')
                ->selectRaw('AVG(DATEDIFF(IFNULL(updated_at, NOW()), data_entrada)) as tempo_medio')
                ->whereNotNull('data_entrada')
                ->whereIn('id', $query->select('id'))
                ->first()->tempo_medio ?? 0;
                
            // Pacotes finalizados (arquivados ou nos quais o pagamento foi concluído)
            $pacotesFinalizados = $query->clone()
                ->where('localizacao_atual', 'Arquivo')
                ->orWhere(function($q) {
                    $q->where('valor_pendente', 0)
                      ->where('valor_fatura', '>', 0);
                })
                ->count();
                
            // Pacotes em andamento (não finalizados)
            $pacotesAndamento = $query->clone()
                ->where('localizacao_atual', '!=', 'Arquivo')
                ->where(function($q) {
                    $q->where('valor_pendente', '>', 0)
                      ->orWhere('valor_fatura', 0);
                })
                ->count();
                
            // Pacotes atrasados (tempo > que o tempo médio * 1.5)
            $pacotesAtrasados = DB::table('pacotes')
                ->whereRaw('DATEDIFF(NOW(), data_entrada) > ?', [$tempoMedio * 1.5])
                ->whereIn('id', $query->select('id'))
                ->where('localizacao_atual', '!=', 'Arquivo')
                ->count();
            
            // Tempo médio por tipo de pacote
            $tempoTipo = DB::table('pacotes')
                ->join('tipos_pacote', 'tipos_pacote.id', '=', 'pacotes.tipo_id')
                ->select(
                    'tipos_pacote.nome',
                    DB::raw('AVG(DATEDIFF(IFNULL(pacotes.updated_at, NOW()), pacotes.data_entrada)) as tempo_medio')
                )
                ->whereIn('pacotes.id', $query->select('id'))
                ->whereNotNull('pacotes.data_entrada')
                ->groupBy('tipos_pacote.nome')
                ->orderBy('tempo_medio', 'desc')
                ->get();
                
            $tempoTipoData = [
                'labels' => $tempoTipo->pluck('nome')->toArray(),
                'values' => $tempoTipo->pluck('tempo_medio')->map(function($value) {
                    return round($value, 1);
                })->toArray()
            ];
            
            // Performance por OCS/PSA
            $performanceOcsPsa = DB::table('pacotes')
                ->join('ocs_psa', 'ocs_psa.id', '=', 'pacotes.ocs_psa_id')
                ->select(
                    'ocs_psa.nome',
                    DB::raw('AVG(DATEDIFF(IFNULL(pacotes.updated_at, NOW()), pacotes.data_entrada)) as tempo_medio'),
                    DB::raw('COUNT(*) as total_pacotes')
                )
                ->whereIn('pacotes.id', $query->select('id'))
                ->whereNotNull('pacotes.data_entrada')
                ->groupBy('ocs_psa.nome')
                ->orderBy('tempo_medio', 'asc')
                ->limit(5)
                ->get();
                
            $performanceOcsPsaData = [
                'labels' => $performanceOcsPsa->pluck('nome')->toArray(),
                'values' => $performanceOcsPsa->pluck('tempo_medio')->map(function($value) {
                    return round($value, 1);
                })->toArray(),
                'pacotes' => $performanceOcsPsa->pluck('total_pacotes')->toArray()
            ];
            
            // Tendência de tempo de processamento
            $tendenciaTempo = [
                'labels' => [],
                'valores' => []
            ];
            
            // Últimos 6 meses
            for ($i = 5; $i >= 0; $i--) {
                $mesInicio = Carbon::now()->subMonths($i)->startOfMonth();
                $mesFim = Carbon::now()->subMonths($i)->endOfMonth();
                
                $tendenciaTempo['labels'][] = $mesInicio->format('M/Y');
                
                $tempoMesMes = DB::table('pacotes')
                    ->selectRaw('AVG(DATEDIFF(IFNULL(updated_at, NOW()), data_entrada)) as tempo_medio')
                    ->whereNotNull('data_entrada')
                    ->whereIn('id', $query->select('id'))
                    ->whereBetween('data_entrada', [$mesInicio, $mesFim])
                    ->first()->tempo_medio ?? 0;
                    
                $tendenciaTempo['valores'][] = round($tempoMesMes, 1);
            }
            
            // Meta vs Realizado
            $metaRealizado = [
                'labels' => [],
                'meta' => [],
                'realizado' => []
            ];
            
            // Meta padrão para tempo médio de processamento (em dias) - pode ser substituída por metas reais do banco de dados
            $metaPadrao = 10;
            
            // Últimos 6 meses
            for ($i = 5; $i >= 0; $i--) {
                $mesInicio = Carbon::now()->subMonths($i)->startOfMonth();
                $mesFim = Carbon::now()->subMonths($i)->endOfMonth();
                
                $metaRealizado['labels'][] = $mesInicio->format('M/Y');
                $metaRealizado['meta'][] = $metaPadrao;
                
                $tempoRealizado = DB::table('pacotes')
                    ->selectRaw('AVG(DATEDIFF(IFNULL(updated_at, NOW()), data_entrada)) as tempo_medio')
                    ->whereNotNull('data_entrada')
                    ->whereIn('id', $query->select('id'))
                    ->whereBetween('data_entrada', [$mesInicio, $mesFim])
                    ->first()->tempo_medio ?? 0;
                    
                $metaRealizado['realizado'][] = round($tempoRealizado, 1);
            }
            
            return response()->json([
                'kpis' => [
                    'tempo_medio' => round($tempoMedio, 1),
                    'pacotes_finalizados' => $pacotesFinalizados,
                    'pacotes_andamento' => $pacotesAndamento,
                    'pacotes_atrasados' => $pacotesAtrasados
                ],
                'tempo_tipo' => $tempoTipoData,
                'performance_ocspsa' => $performanceOcsPsaData,
                'tendencia_tempo' => $tendenciaTempo,
                'meta_realizado' => $metaRealizado
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao carregar dados de performance: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function desempenho(Request $request)
    {
        try {
            $pesos = $this->obterPesosDesempenho();
            $scored = $this->calcularDesempenhoPorColaborador($request, null, null, $pesos);

            $topRanking = $scored->take(10)->values();
            $topEixos = $scored->take(5)->values();
            $melhor = $scored->first();

            $historicoMensal = [
                'labels' => [],
                'valores' => [],
            ];

            for ($i = 5; $i >= 0; $i--) {
                $inicioMes = Carbon::now()->subMonths($i)->startOfMonth();
                $fimMes = Carbon::now()->subMonths($i)->endOfMonth();
                $historicoMensal['labels'][] = $inicioMes->format('m/Y');

                $desempenhoMes = $this->calcularDesempenhoPorColaborador($request, $inicioMes, $fimMes, $pesos);
                $historicoMensal['valores'][] = round((float) ($desempenhoMes->avg('score_operacional') ?? 0), 1);
            }

            return response()->json([
                'kpis' => [
                    'media_score' => round($scored->avg('score_operacional'), 1),
                    'melhor_colaborador' => [
                        'nome' => $melhor['nome'] ?? '-',
                        'score' => $melhor['score_operacional'] ?? 0,
                    ],
                    'total_colaboradores' => $scored->count(),
                    'total_movimentacoes' => (int) $scored->sum('volume_bruto'),
                    'retrabalho_medio' => round($scored->avg('taxa_retrabalho'), 1),
                    'pesos' => $pesos,
                ],
                'ranking' => [
                    'labels' => $topRanking->pluck('nome')->toArray(),
                    'values' => $topRanking->pluck('score_operacional')->toArray(),
                ],
                'eixos' => [
                    'labels' => $topEixos->pluck('nome')->toArray(),
                    'volume' => $topEixos->pluck('scores.volume')->toArray(),
                    'tempo' => $topEixos->pluck('scores.tempo')->toArray(),
                    'qualidade' => $topEixos->pluck('scores.qualidade')->toArray(),
                    'retrabalho' => $topEixos->pluck('scores.retrabalho')->toArray(),
                ],
                'retrabalho' => [
                    'labels' => $topRanking->pluck('nome')->toArray(),
                    'values' => $topRanking->pluck('taxa_retrabalho')->toArray(),
                ],
                'historico_mensal' => $historicoMensal,
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao carregar dados de desempenho: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportarDesempenho(Request $request, $tipo)
    {
        try {
            $pesos = $this->obterPesosDesempenho();
            $scored = $this->calcularDesempenhoPorColaborador($request, null, null, $pesos)->take(50)->values();

            $dados = [
                'gerado_em' => now()->format('d/m/Y H:i:s'),
                'pesos' => $pesos,
                'itens' => $scored->toArray(),
            ];

            if ($tipo === 'csv') {
                return $this->exportarDesempenhoCsv($dados);
            }

            if ($tipo === 'pdf') {
                return $this->exportarDesempenhoPdf($dados);
            }

            return response()->json(['error' => 'Formato não suportado para desempenho'], 400);
        } catch (\Exception $e) {
            Log::error('Erro ao exportar desempenho: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function exportarDesempenhoCsv(array $dados)
    {
        $nomeArquivo = 'desempenho-' . now()->format('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $nomeArquivo . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Relatório de Desempenho Operacional']);
        fputcsv($handle, ['Gerado em', $dados['gerado_em']]);
        fputcsv($handle, ['Pesos', 'Volume=' . $dados['pesos']['volume'] . '% | Tempo=' . $dados['pesos']['tempo'] . '% | Qualidade=' . $dados['pesos']['qualidade'] . '% | Retrabalho=' . $dados['pesos']['retrabalho'] . '%']);
        fputcsv($handle, ['']);
        fputcsv($handle, ['Colaborador', 'Score Operacional', 'Volume', 'Tempo', 'Qualidade', 'Retrabalho', 'Taxa Retrabalho (%)', 'Movimentações']);

        foreach ($dados['itens'] as $item) {
            fputcsv($handle, [
                $item['nome'],
                $item['score_operacional'],
                $item['scores']['volume'] ?? 0,
                $item['scores']['tempo'] ?? 0,
                $item['scores']['qualidade'] ?? 0,
                $item['scores']['retrabalho'] ?? 0,
                $item['taxa_retrabalho'] ?? 0,
                $item['volume_bruto'] ?? 0,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, $headers);
    }

    private function exportarDesempenhoPdf(array $dados)
    {
        $pdf = Pdf::loadView('graficos.export.desempenho_pdf', [
            'dados' => $dados,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('desempenho-' . now()->format('Ymd') . '.pdf');
    }

    private function obterPesosDesempenho(): array
    {
        $pesos = [
            'volume' => Configuracao::obterValorNumerico('desempenho_peso_volume', 25),
            'tempo' => Configuracao::obterValorNumerico('desempenho_peso_tempo', 25),
            'qualidade' => Configuracao::obterValorNumerico('desempenho_peso_qualidade', 25),
            'retrabalho' => Configuracao::obterValorNumerico('desempenho_peso_retrabalho', 25),
        ];

        $soma = array_sum($pesos);
        if ($soma <= 0) {
            return [
                'volume' => 25,
                'tempo' => 25,
                'qualidade' => 25,
                'retrabalho' => 25,
            ];
        }

        return [
            'volume' => round(($pesos['volume'] / $soma) * 100, 2),
            'tempo' => round(($pesos['tempo'] / $soma) * 100, 2),
            'qualidade' => round(($pesos['qualidade'] / $soma) * 100, 2),
            'retrabalho' => round(($pesos['retrabalho'] / $soma) * 100, 2),
        ];
    }

    private function calcularDesempenhoPorColaborador(Request $request, ?Carbon $intervaloInicio = null, ?Carbon $intervaloFim = null, ?array $pesos = null)
    {
        $pesos = $pesos ?? $this->obterPesosDesempenho();

        $query = DB::table('movimentacoes_pacote as mp')
            ->join('users as u', 'u.id', '=', 'mp.usuario_id')
            ->join('pacotes as p', 'p.id', '=', 'mp.pacote_id')
            ->leftJoin('movimentacoes_pacote as mp2', function ($join) {
                $join->on('mp2.pacote_id', '=', 'mp.pacote_id')
                    ->whereRaw('mp2.id = (select min(m3.id) from movimentacoes_pacote m3 where m3.pacote_id = mp.pacote_id and m3.id > mp.id)');
            })
            ->whereNotNull('mp.usuario_id');

        if ($intervaloInicio && $intervaloFim) {
            $query->whereBetween('mp.created_at', [$intervaloInicio, $intervaloFim]);
        } elseif ($request->filled('periodo')) {
            [$dataInicio, $dataFim] = explode(' - ', $request->periodo);
            $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicio)->startOfDay();
            $dataFim = Carbon::createFromFormat('d/m/Y', $dataFim)->endOfDay();
            $query->whereBetween('mp.created_at', [$dataInicio, $dataFim]);
        }

        if ($request->filled('ocs_psa_id') && $request->ocs_psa_id !== 'todos') {
            $query->where('p.ocs_psa_id', $request->ocs_psa_id);
        }

        if ($request->filled('tipo_id') && $request->tipo_id !== 'todos') {
            $query->where('p.tipo_id', $request->tipo_id);
        }

        if ($request->filled('tipo_conta_id') && $request->tipo_conta_id !== 'todos') {
            $query->where('p.tipo_conta_id', $request->tipo_conta_id);
        }

        if ($request->filled('estado_glosa') && $request->estado_glosa !== 'todos') {
            $query->where('p.estado_glosa', $request->estado_glosa);
        }

        $dadosBrutos = $query
            ->select(
                'u.id as usuario_id',
                'u.name as usuario_nome',
                DB::raw('COUNT(mp.id) as total_movimentacoes'),
                DB::raw("SUM(CASE WHEN LOWER(CONCAT(COALESCE(mp.acao, ''), ' ', COALESCE(mp.mensagem, ''), ' ', COALESCE(mp.observacao, ''))) REGEXP '(retrabalho|reprocess(amento|ar)?|corre(c|ç)(a|ã)o( de)?|devolu(c|ç)(a|ã)o para corre(c|ç)(a|ã)o|rean[aá]lise|ajuste de inconsist[êe]ncia|retornad[oa] para corre(c|ç)(a|ã)o)' THEN 1 ELSE 0 END) as total_retrabalho"),
                DB::raw('AVG(CASE WHEN mp2.created_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, mp.created_at, mp2.created_at) END) as tempo_medio_horas')
            )
            ->groupBy('u.id', 'u.name')
            ->get();

        if ($dadosBrutos->isEmpty()) {
            return collect();
        }

        $metricas = $dadosBrutos->map(function ($item) {
            $totalMovimentacoes = (int) $item->total_movimentacoes;
            $totalRetrabalho = (int) $item->total_retrabalho;
            $taxaRetrabalho = $totalMovimentacoes > 0 ? ($totalRetrabalho / $totalMovimentacoes) * 100 : 0;

            return [
                'nome' => $item->usuario_nome,
                'volume_bruto' => $totalMovimentacoes,
                'tempo_bruto' => max(0, (float) ($item->tempo_medio_horas ?? 0)),
                'taxa_retrabalho' => round($taxaRetrabalho, 2),
                'qualidade_bruta' => round(100 - $taxaRetrabalho, 2),
            ];
        });

        $maxVolume = max(1, (int) $metricas->max('volume_bruto'));
        $maxTempo = (float) $metricas->max('tempo_bruto');
        $minTempo = (float) $metricas->min('tempo_bruto');

        return $metricas->map(function ($item) use ($maxVolume, $maxTempo, $minTempo, $pesos) {
            $scoreVolume = round(($item['volume_bruto'] / $maxVolume) * 100, 1);

            if ($maxTempo == $minTempo) {
                $scoreTempo = $item['tempo_bruto'] > 0 ? 100 : 0;
            } else {
                $scoreTempo = round((($maxTempo - $item['tempo_bruto']) / ($maxTempo - $minTempo)) * 100, 1);
            }

            $scoreQualidade = round($item['qualidade_bruta'], 1);
            $scoreRetrabalho = round(100 - $item['taxa_retrabalho'], 1);

            $scoreOperacional = round(
                (
                    ($scoreVolume * $pesos['volume']) +
                    ($scoreTempo * $pesos['tempo']) +
                    ($scoreQualidade * $pesos['qualidade']) +
                    ($scoreRetrabalho * $pesos['retrabalho'])
                ) / 100,
                1
            );

            return [
                'nome' => $item['nome'],
                'score_operacional' => $scoreOperacional,
                'scores' => [
                    'volume' => $scoreVolume,
                    'tempo' => $scoreTempo,
                    'qualidade' => $scoreQualidade,
                    'retrabalho' => $scoreRetrabalho,
                ],
                'taxa_retrabalho' => $item['taxa_retrabalho'],
                'volume_bruto' => $item['volume_bruto'],
            ];
        })->sortByDesc('score_operacional')->values();
    }

    public function exportar($tipo)
    {
        try {
            // Obter dados para exportação
            $data = [
                'data_geracao' => now()->format('d/m/Y H:i:s'),
                'filtros' => request()->all()
            ];
            
            // Buscar dados do dashboard
            $query = Pacote::query();
            
            // Aplicar filtros
            if (request()->filled('periodo')) {
                [$dataInicio, $dataFim] = explode(' - ', request()->periodo);
                $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicio)->startOfDay();
                $dataFim = Carbon::createFromFormat('d/m/Y', $dataFim)->endOfDay();
                $query->whereBetween('data_entrada', [$dataInicio, $dataFim]);
            }
            
            if (request()->filled('ocs_psa_id') && request()->ocs_psa_id !== 'todos') {
                $query->where('ocs_psa_id', request()->ocs_psa_id);
            }
            
            if (request()->filled('tipo_id') && request()->tipo_id !== 'todos') {
                $query->where('tipo_id', request()->tipo_id);
            }
            
            if (request()->filled('tipo_conta_id') && request()->tipo_conta_id !== 'todos') {
                $query->where('tipo_conta_id', request()->tipo_conta_id);
            }
            
            if (request()->filled('estado_glosa') && request()->estado_glosa !== 'todos') {
                $query->where('estado_glosa', request()->estado_glosa);
            }
            
            // Calcular métricas
            $totalPacotes = $query->count();
            $valorTotalFaturas = $query->sum('valor_fatura');
            $valorTotalGlosas = $query->sum('valor_glosa');
            $taxaMediaGlosa = $valorTotalFaturas > 0 ? ($valorTotalGlosas / $valorTotalFaturas * 100) : 0;
            
            // Calcular tempo médio (dias entre data_entrada e data mais recente)
            $tempoMedioDias = DB::table('pacotes')
                ->selectRaw('AVG(DATEDIFF(IFNULL(updated_at, NOW()), data_entrada)) as media_dias')
                ->whereNotNull('data_entrada')
                ->whereIn('id', $query->select('id'))
                ->first()->media_dias ?? 0;
                
            // Adicionar KPIs aos dados
            $data['kpis'] = [
                'total_pacotes' => $totalPacotes,
                'valor_total_faturas' => $valorTotalFaturas,
                'taxa_media_glosa' => round($taxaMediaGlosa, 2),
                'tempo_medio_dias' => round($tempoMedioDias, 1)
            ];
            
            // Adicionar relatórios específicos
            // Status dos pacotes
            $status = DB::table('pacotes')
                ->select('localizacao_atual', DB::raw('count(*) as total'))
                ->whereIn('id', $query->select('id'))
                ->groupBy('localizacao_atual')
                ->get()
                ->pluck('total', 'localizacao_atual')
                ->toArray();
                
            $data['status'] = $status;
            
            // Distribuição por OCS/PSA
            $ocsPsa = DB::table('pacotes')
                ->join('ocs_psa', 'ocs_psa.id', '=', 'pacotes.ocs_psa_id')
                ->select('ocs_psa.nome', DB::raw('count(*) as total'))
                ->whereIn('pacotes.id', $query->select('id'))
                ->groupBy('ocs_psa.nome')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get()
                ->pluck('total', 'nome')
                ->toArray();
                
            $data['distribuicao_ocspsa'] = $ocsPsa;
            
            // Definir nome do arquivo
            $filename = 'dashboard-' . now()->format('Ymd') . '.' . $tipo;
            
            // Gerar arquivo com base no tipo solicitado
            switch ($tipo) {
                case 'excel':
                    return $this->exportarExcel($data, $filename);
                    
                case 'pdf':
                    return $this->exportarPDF($data, $filename);
                    
                case 'csv':
                    return $this->exportarCSV($data, $filename);
                    
                default:
                    return response()->json(['error' => 'Formato não suportado'], 400);
            }
        } catch (\Exception $e) {
            Log::error("Erro ao exportar dados: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Exporta os dados para Excel
     */
    private function exportarExcel($data, $filename)
    {
        // Cabeçalho para download
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        // Para uma implementação completa, você precisaria usar uma biblioteca como PhpSpreadsheet.
        // Por enquanto, vamos retornar os dados em formato JSON como alternativa.
        return response()->json($data, 200, $headers);
    }

    /**
     * Exporta os dados para PDF
     */
    private function exportarPDF($data, $filename)
    {
        // Cabeçalho para download
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        // Para uma implementação completa, você precisaria usar uma biblioteca como DomPDF.
        // Por enquanto, vamos retornar os dados em formato JSON como alternativa.
        return response()->json($data, 200, $headers);
    }

    /**
     * Exporta os dados para CSV
     */
    private function exportarCSV($data, $filename)
    {
        // Cabeçalho para download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        // Cria um arquivo CSV na memória
        $handle = fopen('php://temp', 'r+');
        
        // Cabeçalho do CSV
        fputcsv($handle, ['Dashboard Analítico Estratégico - Gerado em ' . $data['data_geracao']]);
        fputcsv($handle, ['']);
        
        // Seção de KPIs
        fputcsv($handle, ['KPIs Principais']);
        fputcsv($handle, ['Métrica', 'Valor']);
        fputcsv($handle, ['Total de Pacotes', $data['kpis']['total_pacotes']]);
        fputcsv($handle, ['Valor Total das Faturas', 'R$ ' . number_format($data['kpis']['valor_total_faturas'], 2, ',', '.')]);
        fputcsv($handle, ['Taxa Média de Glosa', number_format($data['kpis']['taxa_media_glosa'], 2, ',', '.') . '%']);
        fputcsv($handle, ['Tempo Médio (dias)', number_format($data['kpis']['tempo_medio_dias'], 1, ',', '.')]);
        fputcsv($handle, ['']);
        
        // Seção de Status
        fputcsv($handle, ['Distribuição por Status']);
        fputcsv($handle, ['Status', 'Quantidade']);
        foreach ($data['status'] as $status => $quantidade) {
            fputcsv($handle, [$status, $quantidade]);
        }
        fputcsv($handle, ['']);
        
        // Seção de OCS/PSA
        fputcsv($handle, ['Top OCS/PSA']);
        fputcsv($handle, ['OCS/PSA', 'Quantidade']);
        foreach ($data['distribuicao_ocspsa'] as $ocsPsa => $quantidade) {
            fputcsv($handle, [$ocsPsa, $quantidade]);
        }
        
        // Voltar para o início do arquivo
        rewind($handle);
        
        // Obter o conteúdo do CSV
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv, 200, $headers);
    }
}