<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pacote;
use App\Models\OcsPsa; // Adicione esta linha
use App\Models\User;
use App\Models\MovimentacaoPacote;
use App\Models\MotivoGlosa;
use App\Models\TipoPacote;
use App\Models\TipoConta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    /**
     * Exibe o painel principal de relatórios
     */
    public function index()
    {
        // Contagens de pacotes por localização
        $totalPacotes = Pacote::count();
        
        // Status dos pacotes
        $pacotesPorStatus = [
            'protocolo' => Pacote::where('localizacao_atual', 'Protocolo')->count(),
            'analise' => Pacote::where('localizacao_atual', 'Lisura')->count(),
            'aguardando' => Pacote::where('estado_geral', 'Aguardando Limite de Crédito')->count(),
            'glosa' => Pacote::where('localizacao_atual', 'Glosa')->count(),
            'finalizado' => Pacote::whereNotNull('data_entrada')
                ->where('estado_geral', 'Finalizado')
                ->count(),
        ];
        
        // Dados financeiros
        $valorFaturadoTotal = Pacote::sum('valor_fatura');
        $valorGlosaTotal = Pacote::sum('valor_glosa');
        $taxaGlosa = $valorFaturadoTotal > 0 ? ($valorGlosaTotal / $valorFaturadoTotal * 100) : 0;
        
        // Métricas de processamento
        $diasMedios = Pacote::whereNotNull('data_entrada')
            ->whereNotNull('data_notificacao_glosa')
            ->selectRaw('AVG(DATEDIFF(data_notificacao_glosa, data_entrada)) as media_dias')
            ->first()->media_dias ?? 0;
        
        // Top 5 OCS/PSA
        $topOcsPsa = Pacote::select('ocs_psa_id', DB::raw('SUM(valor_fatura) as total'))
            ->with('ocsPsa')
            ->groupBy('ocs_psa_id')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();
        
        // Últimos 4 meses para os gráficos
        $periodoMeses = [];
        $dadosMensais = [];
        
        for ($i = 3; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $periodoMeses[] = $mes->format('M/Y');
            
            $valorMensal = Pacote::whereYear('data_entrada', $mes->year)
                ->whereMonth('data_entrada', $mes->month)
                ->sum('valor_fatura');
                
            $valorGlosadoMensal = Pacote::whereYear('data_entrada', $mes->year)
                ->whereMonth('data_entrada', $mes->month)
                ->sum('valor_glosa');
                
            $dadosMensais[] = [
                'valor_faturado' => $valorMensal,
                'valor_glosado' => $valorGlosadoMensal,
                'valor_liquido' => $valorMensal - $valorGlosadoMensal
            ];
        }
        
        return view('relatorios.index', compact(
            'totalPacotes', 
            'pacotesPorStatus', 
            'valorFaturadoTotal',
            'valorGlosaTotal', 
            'taxaGlosa', 
            'diasMedios',
            'topOcsPsa',
            'periodoMeses',
            'dadosMensais'
        ));
    }

    /**
     * Exibe relatório de status dos pacotes
     */
    public function statusPacotes(Request $request)
    {
        // Definir os filtros padrão e aplicar os do request quando presentes
        $filtros = [
            'status' => $request->input('status', null),
            'localizacao' => $request->input('localizacao', null),
            'tipo' => $request->input('tipo', null),
            'data_inicio' => $request->has('periodo') ? 
                Carbon::createFromFormat('d/m/Y', explode(' - ', $request->input('periodo'))[0]) : 
                Carbon::now()->startOfMonth(),
            'data_fim' => $request->has('periodo') ? 
                Carbon::createFromFormat('d/m/Y', explode(' - ', $request->input('periodo'))[1]) : 
                Carbon::now()->endOfMonth(),
            'ocs_psa_id' => $request->input('ocs_psa_id', null),
        ];
        
        // Query base
        $query = Pacote::with(['ocsPsa', 'tipoPacote'])
            ->whereBetween('data_entrada', [$filtros['data_inicio'], $filtros['data_fim']]);
            
        // Aplicar filtros condicionais
        if ($filtros['status']) {
            $query->where('ultima_acao', 'like', '%' . $filtros['status'] . '%');
        }
        
        if ($filtros['localizacao']) {
            $query->where('localizacao_atual', $filtros['localizacao']);
        }
        
        if ($filtros['ocs_psa_id']) {
            $query->where('ocs_psa_id', $filtros['ocs_psa_id']);
        }
        
        // Aplicar tipos de relatório pré-formatados
        if ($request->has('tipo')) {
            switch ($request->input('tipo')) {
                case 'atrasados':
                    $query->where('ultima_acao', 'like', '%atrasado%');
                    break;
                case 'criticos':
                    $query->where('ultima_acao', 'like', '%critico%');
                    break;
                case 'hoje':
                    $query->whereDate('data_entrada', Carbon::today());
                    break;
                case 'semana':
                    $query->whereBetween('data_entrada', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'alto_valor':
                    $query->where('valor_fatura', '>', 10000)->orderBy('valor_fatura', 'desc');
                    break;
                case 'aguardando_recurso':
                    $query->where('estado_glosa', 'Aguardando Recurso');
                    break;
                case 'tempo_excessivo':
                    $query->whereRaw('DATEDIFF(NOW(), data_entrada) > 30');
                    break;
                case 'finalizados':
                    $query->where('estado_geral', 'Finalizado');
                    break;
            }
        }
        
        // Recuperar pacotes
        $pacotes = $query->get();
        
        // Contagens para o painel de status
        $contagens = [
            'total' => $pacotes->count(),
            'normal' => $pacotes->where('ultima_acao', 'like', '%normal%')->count(),
            'atrasado' => $pacotes->where('ultima_acao', 'like', '%atrasado%')->count(),
            'critico' => $pacotes->where('ultima_acao', 'like', '%critico%')->count(),
            'aguardando_recurso' => $pacotes->where('estado_glosa', 'Aguardando Recurso')->count(),
        ];
        
        // Estatísticas de tempo para gráficos
        $tempoPorStatus = [
            'normal' => $pacotes->where('ultima_acao', 'like', '%normal%')
                ->average(function($pacote) {
                    return Carbon::parse($pacote->data_entrada)->diffInDays(Carbon::now());
                }) ?? 0,
            'atrasado' => $pacotes->where('ultima_acao', 'like', '%atrasado%')
                ->average(function($pacote) {
                    return Carbon::parse($pacote->data_entrada)->diffInDays(Carbon::now());
                }) ?? 0,
            'critico' => $pacotes->where('ultima_acao', 'like', '%critico%')
                ->average(function($pacote) {
                    return Carbon::parse($pacote->data_entrada)->diffInDays(Carbon::now());
                }) ?? 0,
            'aguardando_recurso' => $pacotes->where('estado_glosa', 'Aguardando Recurso')
                ->average(function($pacote) {
                    return Carbon::parse($pacote->data_entrada)->diffInDays(Carbon::now());
                }) ?? 0,
        ];
        
        // Estatísticas por localização
        $localizacoes = ['Protocolo', 'Lisura', 'SIRE', 'Glosa', 'Arquivo'];
        $statusPorLocalizacao = [];
        
        foreach ($localizacoes as $localizacao) {
            $statusPorLocalizacao[$localizacao] = [
                'normal' => $pacotes->where('localizacao_atual', $localizacao)
                    ->where('ultima_acao', 'like', '%normal%')->count(),
                'atrasado' => $pacotes->where('localizacao_atual', $localizacao)
                    ->where('ultima_acao', 'like', '%atrasado%')->count(),
                'critico' => $pacotes->where('localizacao_atual', $localizacao)
                    ->where('ultima_acao', 'like', '%critico%')->count(),
                'aguardando_recurso' => $pacotes->where('localizacao_atual', $localizacao)
                    ->where('estado_glosa', 'Aguardando Recurso')->count(),
            ];
        }
        
        // Dados para gráfico de evolução de status
        $evolucaoMeses = [];
        $evolucaoStatus = [
            'total' => [],
            'atrasado' => [],
            'critico' => []
        ];
        
        for ($i = 3; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $evolucaoMeses[] = $mes->format('M/Y');
            
            $totalMes = Pacote::whereYear('data_entrada', $mes->year)
                ->whereMonth('data_entrada', $mes->month)
                ->count();
                
            $atrasadosMes = Pacote::whereYear('data_entrada', $mes->year)
                ->whereMonth('data_entrada', $mes->month)
                ->where('ultima_acao', 'like', '%atrasado%')
                ->count();
                
            $criticosMes = Pacote::whereYear('data_entrada', $mes->year)
                ->whereMonth('data_entrada', $mes->month)
                ->where('ultima_acao', 'like', '%critico%')
                ->count();
                
            $evolucaoStatus['total'][] = $totalMes;
            $evolucaoStatus['atrasado'][] = $atrasadosMes;
            $evolucaoStatus['critico'][] = $criticosMes;
        }
        
        return view('relatorios.status-pacotes', compact(
            'pacotes', 
            'contagens', 
            'tempoPorStatus',
            'statusPorLocalizacao',
            'evolucaoMeses',
            'evolucaoStatus',
            'filtros'
        ));
    }

    /**
     * Exibe relatório de performance e produtividade
     */
    public function performance(Request $request)
    {
        // Aplicar filtros da request
        $dataInicio = $request->has('periodo') ? 
            Carbon::createFromFormat('d/m/Y', explode(' - ', $request->input('periodo'))[0]) : 
            Carbon::now()->subDays(30);
        $dataFim = $request->has('periodo') ? 
            Carbon::createFromFormat('d/m/Y', explode(' - ', $request->input('periodo'))[1]) : 
            Carbon::now();
            
        // Tempos médios de processamento por setor
        $setores = ['Protocolo', 'Lisura', 'SIRE', 'Glosa', 'Arquivo'];
        $tempoMedioPorSetor = [];
        $pacotesPorSetor = [];
        
        foreach ($setores as $setor) {
            $movimentacoes = MovimentacaoPacote::where('setor_destino', $setor)
                ->whereBetween('created_at', [$dataInicio, $dataFim])
                ->get();
                
            $tempoTotal = 0;
            $count = 0;
            
            foreach ($movimentacoes as $mov) {
                $saidaMovimentacao = MovimentacaoPacote::where('pacote_id', $mov->pacote_id)
                    ->where('origem', $setor)
                    ->where('created_at', '>', $mov->created_at)
                    ->orderBy('created_at', 'asc')
                    ->first();
                    
                if ($saidaMovimentacao) {
                    $tempoTotal += $mov->created_at->diffInHours($saidaMovimentacao->created_at) / 24; // Dias
                    $count++;
                }
            }
            
            $tempoMedioPorSetor[$setor] = $count > 0 ? round($tempoTotal / $count, 1) : 0;
            $pacotesPorSetor[$setor] = $movimentacoes->pluck('pacote_id')->unique()->count();
        }
        
        // Produtividade mensal
        $meses = [];
        $produtividadeMensal = [];
        
        for ($i = 3; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $meses[] = $mes->format('M/Y');
            
            $produtividadeMensal[] = Pacote::whereYear('data_entrada', $mes->year)
                ->whereMonth('data_entrada', $mes->month)
                ->count();
        }
        
        // Volume por tipo de pacote
        $tiposPacote = Pacote::select('tipo_id', DB::raw('COUNT(*) as total'))
            ->with('tipoPacote')
            ->whereBetween('data_entrada', [$dataInicio, $dataFim])
            ->groupBy('tipo_id')
            ->get();
            
        $volumePorTipo = [];
        $volumePorTipoLabels = [];
        
        foreach ($tiposPacote as $tipo) {
            if ($tipo->tipoPacote) {
                $volumePorTipoLabels[] = $tipo->tipoPacote->nome;
                $volumePorTipo[] = $tipo->total;
            }
        }
        
        // Performance por usuário
        $usuariosMovimentacao = MovimentacaoPacote::select('usuario_id', DB::raw('COUNT(*) as total_movimentacoes'))
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->with('usuario')
            ->groupBy('usuario_id')
            ->orderBy('total_movimentacoes', 'desc')
            ->take(10)
            ->get();
            
        // Identificação de gargalos
        $gargalos = [];
        
        foreach ($setores as $setor) {
            $pacotesAtrasados = Pacote::where('localizacao_atual', $setor)
                ->where('ultima_acao', 'like', '%atrasado%')
                ->count();
                
            $pacotesCriticos = Pacote::where('localizacao_atual', $setor)
                ->where('ultima_acao', 'like', '%critico%')
                ->count();
                
            $gargalos[$setor] = [
                'atrasados' => $pacotesAtrasados,
                'criticos' => $pacotesCriticos,
                'total_problemas' => $pacotesAtrasados + $pacotesCriticos
            ];
        }
        
        return view('relatorios.performance', compact(
            'tempoMedioPorSetor',
            'pacotesPorSetor',
            'meses',
            'produtividadeMensal',
            'volumePorTipo',
            'volumePorTipoLabels',
            'usuariosMovimentacao',
            'gargalos',
            'setores'
        ));
    }

    /**
     * Exibe relatório de glosas
     */
    public function glosas(Request $request)
    {
        // Filtros
        $periodo = $request->input('periodo', now()->startOfYear()->format('d/m/Y') . ' - ' . now()->endOfYear()->format('d/m/Y'));
        [$dataInicio, $dataFim] = explode(' - ', $periodo);
        $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicio)->startOfDay();
        $dataFim = Carbon::createFromFormat('d/m/Y', $dataFim)->endOfDay();

        $ocsPsaId = $request->input('ocs_psa_id');

        // Query base
        $query = Pacote::select(
            'ocs_psa.nome',
            DB::raw('SUM(pacotes.valor_fatura) as valor_faturado'),
            DB::raw('SUM(pacotes.valor_glosa) - SUM(pacotes.valor_deferido) as valor_glosado'),
            DB::raw('IF(SUM(pacotes.valor_fatura) > 0, ((SUM(pacotes.valor_glosa) - SUM(pacotes.valor_deferido)) / SUM(pacotes.valor_fatura)) * 100, 0) as percentual_glosa')
        )
        ->join('ocs_psa', 'ocs_psa.id', '=', 'pacotes.ocs_psa_id')
        ->whereBetween('pacotes.data_entrada', [$dataInicio, $dataFim])
        ->groupBy('ocs_psa.nome');

        if ($ocsPsaId) {
            $query->where('ocs_psa.id', $ocsPsaId);
        }

        $percentuaisGlosa = $query->get()->toArray();

        // Lista de OCS/PSA
        $ocsPsaList = OcsPsa::orderBy('nome')->get();

        return view('relatorios.glosas', compact('percentuaisGlosa', 'ocsPsaList'));
    }

    /**
     * Exibe relatório financeiro
     */
    public function financeiro(Request $request)
    {
        // Filtros
        $periodo = $request->input('periodo', now()->startOfMonth()->format('d/m/Y') . ' - ' . now()->endOfMonth()->format('d/m/Y'));
        [$dataInicio, $dataFim] = explode(' - ', $periodo);
        $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicio)->startOfDay();
        $dataFim = Carbon::createFromFormat('d/m/Y', $dataFim)->endOfDay();

        $ocsPsaId = $request->input('ocs_psa_id');

        // Query base
        $query = Pacote::whereBetween('data_entrada', [$dataInicio, $dataFim]);

        if ($ocsPsaId) {
            $query->where('ocs_psa_id', $ocsPsaId);
        }

        // Dados financeiros
        $totalFaturado = $query->sum('valor_fatura');
        $totalGlosado = $query->sum('valor_glosa');
        $totalPago = $query->sum('valor_pago');
        $totalPendente = $query->sum('valor_pendente');

        // Evolução mensal
        $evolucaoMensal = [
            'meses' => [],
            'faturado' => [],
            'pago' => []
        ];

        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $inicioMes = $mes->copy()->startOfMonth();
            $fimMes = $mes->copy()->endOfMonth();

            $evolucaoMensal['meses'][] = $mes->format('M/Y');
            $evolucaoMensal['faturado'][] = Pacote::whereBetween('data_entrada', [$inicioMes, $fimMes])->sum('valor_fatura');
            $evolucaoMensal['pago'][] = Pacote::whereBetween('data_entrada', [$inicioMes, $fimMes])->sum('valor_pago');
        }

        // Lista de OCS/PSA
        $ocsPsaList = OcsPsa::orderBy('nome')->get();

        return view('relatorios.financeiro', compact(
            'totalFaturado',
            'totalGlosado',
            'totalPago',
            'totalPendente',
            'evolucaoMensal',
            'ocsPsaList'
        ));
    }

    /**
     * Exibe relatório de OCS/PSA
     */
    public function ocspsa()
    {
        // Obter os dados reais para os gráficos e tabelas
        $topOcsPsaValor = OcsPsa::select('ocs_psa.nome', DB::raw('SUM(pacotes.valor_fatura) as valor'))
            ->join('pacotes', 'ocs_psa.id', '=', 'pacotes.ocs_psa_id')
            ->groupBy('ocs_psa.nome')
            ->orderByDesc('valor')
            ->take(10)
            ->get();

        $topOcsPsaVolume = OcsPsa::select('ocs_psa.nome', DB::raw('COUNT(pacotes.id) as quantidade'))
            ->join('pacotes', 'ocs_psa.id', '=', 'pacotes.ocs_psa_id')
            ->groupBy('ocs_psa.nome')
            ->orderByDesc('quantidade')
            ->take(10)
            ->get();

        $topOcsPsaGlosa = OcsPsa::select('ocs_psa.nome', DB::raw('SUM(pacotes.valor_glosa) as valor_glosado, (SUM(pacotes.valor_glosa) / SUM(pacotes.valor_fatura)) * 100 as taxa_glosa'))
            ->join('pacotes', 'ocs_psa.id', '=', 'pacotes.ocs_psa_id')
            ->groupBy('ocs_psa.nome')
            ->orderByDesc('taxa_glosa')
            ->take(10)
            ->get();

        $topOcsPsaRecuperacao = OcsPsa::select('ocs_psa.nome', DB::raw('SUM(pacotes.valor_deferido) as valor_recuperado, (SUM(pacotes.valor_deferido) / SUM(pacotes.valor_glosa)) * 100 as taxa_recuperacao'))
            ->join('pacotes', 'ocs_psa.id', '=', 'pacotes.ocs_psa_id')
            ->groupBy('ocs_psa.nome')
            ->orderByDesc('taxa_recuperacao')
            ->take(10)
            ->get();

        // Retorne a view com os dados
        return view('relatorios.ocspsa', compact('topOcsPsaValor', 'topOcsPsaVolume', 'topOcsPsaGlosa', 'topOcsPsaRecuperacao'));
    }

    /**
     * Relatório de pacotes com prazo de recurso de glosa vencido —
     * mais de N dias desde a retirada do Ofício de Glosa, ainda
     * aguardando recurso.
     *
     * Este relatório é aviso, não ação (docs/40-decisoes/ADR-12.md): ele
     * não move nenhum pacote sozinho. A movimentação para Arquivo/SIRE é
     * feita pelo operador, via "Registrar recurso não recebido"
     * (specs/003-relatorio-prazo-glosa).
     */
    public function prazoRecurso(Request $request)
    {
        $dias = (int) $request->input('dias', config('pmed2.prazo_recurso_dias'));
        if ($dias < 1) {
            $dias = config('pmed2.prazo_recurso_dias');
        }

        $pacotes = Pacote::validos()
            ->prazoRecursoVencido($dias)
            ->with('ocsPsa')
            ->orderBy('data_retirada_oficio')
            ->get();

        return view('relatorios.prazo-recurso', compact('pacotes', 'dias'));
    }
}