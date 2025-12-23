<?php

namespace App\Helpers;

use App\Models\Pacote;
use App\Models\OcsPsa;
use Illuminate\Support\Facades\DB;

class DashboardHelper
{
    /**
     * Retorna as estatísticas dos pacotes por localização
     *
     * @return array
     */
    public static function getEstatisticasPacotes()
    {
        $localizacoes = ['Protocolo', 'Lisura', 'SIRE', 'Glosa', 'Arquivo'];
        $estatisticas = [];
        
        foreach ($localizacoes as $localizacao) {
            $count = Pacote::where('localizacao_atual', $localizacao)->count();
            $valor = Pacote::where('localizacao_atual', $localizacao)->sum('valor_fatura');
            
            $estatisticas[$localizacao] = [
                'count' => $count,
                'valor' => $valor,
            ];
        }
        
        // Adicionar pacotes arquivados (deleted_at não nulo)
        $estatisticas['Arquivados'] = [
            'count' => Pacote::onlyTrashed()->count(),
            'valor' => Pacote::onlyTrashed()->sum('valor_fatura'),
        ];
        
        return $estatisticas;
    }
    
    /**
     * Retorna os pacotes com alertas de prazo próximo
     *
     * @param int $diasLimite Número de dias para considerar como alerta
     * @param int $limite Quantidade máxima de pacotes a retornar
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPacotesComAlerta($diasLimite = 5, $limite = 5)
    {
        return Pacote::whereNotNull('data_limite_retirada')
            ->whereRaw('data_limite_retirada >= CURDATE()')
            ->whereRaw('DATEDIFF(data_limite_retirada, CURDATE()) <= ?', [$diasLimite])
            ->with('ocsPsa')
            ->limit($limite)
            ->get();
    }
    
    /**
     * Retorna as top OCS/PSAs por quantidade de pacotes
     *
     * @param int $limite Quantidade máxima de OCS/PSAs a retornar
     * @return \Illuminate\Support\Collection
     */
    public static function getTopOcsPsas($limite = 5)
    {
        return DB::table('pacotes')
            ->join('ocs_psa', 'pacotes.ocs_psa_id', '=', 'ocs_psa.id')
            ->select('ocs_psa.id', 'ocs_psa.nome', 
                    DB::raw('COUNT(*) as total_pacotes'),
                    DB::raw('SUM(pacotes.valor_fatura) as valor_total'),
                    DB::raw('SUM(pacotes.valor_glosa) as valor_glosa'))
            ->groupBy('ocs_psa.id', 'ocs_psa.nome')
            ->orderBy('total_pacotes', 'desc')
            ->limit($limite)
            ->get();
    }
    
    /**
     * Retorna métricas de desempenho do sistema
     *
     * @return array
     */
    public static function getDesempenhoSistema()
    {
        $totalPacotes = Pacote::count();
        $pacotesProcessados = Pacote::whereIn('localizacao_atual', ['Arquivo'])->orWhereNotNull('deleted_at')->count();
        $percentualConcluido = $totalPacotes > 0 ? round(($pacotesProcessados / $totalPacotes) * 100, 1) : 0;
        
        $valorTotal = Pacote::sum('valor_fatura');
        $valorPago = Pacote::sum('valor_pago');
        $valorGlosado = Pacote::sum('valor_glosa');
        
        $percentualGlosa = $valorTotal > 0 ? round(($valorGlosado / $valorTotal) * 100, 2) : 0;
        
        return [
            'total_pacotes' => $totalPacotes,
            'pacotes_processados' => $pacotesProcessados,
            'percentual_concluido' => $percentualConcluido,
            'valor_total' => $valorTotal,
            'valor_pago' => $valorPago,
            'valor_glosado' => $valorGlosado,
            'percentual_glosa' => $percentualGlosa,
        ];
    }
}