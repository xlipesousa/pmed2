<?php

namespace App\Helpers;

use App\Models\MovimentacaoPacote;
use App\Models\Pacote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AtividadesHelper
{
    public static function registrar($pacoteId, $acao, $mensagem, $observacao = null, $usuarioId = null)
    {
        return MovimentacaoPacote::create([
            'pacote_id' => $pacoteId,
            'acao' => $acao,
            'mensagem' => $mensagem,
            'observacao' => $observacao,
            'localizacao_pos_acao' => 'Sistema',
            'estado_geral' => 'Normal',
            'estado_glosa' => 'pendente',
            'usuario_id' => $usuarioId ?? auth()->id() ?? 1,
        ]);
    }

    /**
     * Retorna as atividades recentes do sistema
     *
     * @param int $limite Limite de registros a retornar
     * @return \Illuminate\Support\Collection
     */
    public static function getAtividadesRecentes($limite = 10)
    {
        // Obter movimentações recentes de pacotes
        $movimentacoes = MovimentacaoPacote::with(['pacote', 'usuario'])
            ->orderBy('created_at', 'desc')
            ->limit($limite)
            ->get()
            ->map(function ($movimentacao) {
                return [
                    'tipo' => 'movimentacao',
                    'icone' => self::getIconeMovimentacao($movimentacao->tipo_movimentacao),
                    'cor' => self::getCorMovimentacao($movimentacao->tipo_movimentacao),
                    'titulo' => self::getTituloMovimentacao($movimentacao),
                    'descricao' => $movimentacao->observacao ?? 'Sem observação',
                    'usuario' => $movimentacao->usuario ? $movimentacao->usuario->name : 'Sistema',
                    'data' => $movimentacao->created_at,
                    'url' => route('pacotes.show', $movimentacao->pacote_id)
                ];
            });

        return $movimentacoes;
    }

    /**
     * Retorna o ícone para um tipo de movimentação
     *
     * @param string $tipoMovimentacao
     * @return string
     */
    private static function getIconeMovimentacao($tipoMovimentacao)
    {
        $icones = [
            'entrada' => 'fas fa-sign-in-alt',
            'saida' => 'fas fa-sign-out-alt',
            'glosa' => 'fas fa-exclamation-triangle',
            'pagamento' => 'fas fa-money-bill-wave',
            'arquivamento' => 'fas fa-archive',
            'recurso' => 'fas fa-reply',
        ];

        return $icones[strtolower($tipoMovimentacao)] ?? 'fas fa-exchange-alt';
    }

    /**
     * Retorna a cor para um tipo de movimentação
     *
     * @param string $tipoMovimentacao
     * @return string
     */
    private static function getCorMovimentacao($tipoMovimentacao)
    {
        $cores = [
            'entrada' => 'success',
            'saida' => 'primary',
            'glosa' => 'danger',
            'pagamento' => 'info',
            'arquivamento' => 'secondary',
            'recurso' => 'warning',
        ];

        return $cores[strtolower($tipoMovimentacao)] ?? 'info';
    }

    /**
     * Retorna o título para uma movimentação
     *
     * @param MovimentacaoPacote $movimentacao
     * @return string
     */
    private static function getTituloMovimentacao($movimentacao)
    {
        if (!$movimentacao->pacote) {
            return "Pacote movimentado ({$movimentacao->tipo_movimentacao})";
        }

        $nomePrestador = $movimentacao->pacote->ocsPsa ? $movimentacao->pacote->ocsPsa->nome : 'Desconhecido';
        
        switch(strtolower($movimentacao->tipo_movimentacao)) {
            case 'entrada':
                return "Pacote #{$movimentacao->pacote_id} ({$nomePrestador}) recebido em {$movimentacao->destino}";
            case 'saida':
                return "Pacote #{$movimentacao->pacote_id} ({$nomePrestador}) enviado para {$movimentacao->destino}";
            case 'glosa':
                return "Glosa aplicada no pacote #{$movimentacao->pacote_id} ({$nomePrestador})";
            case 'pagamento':
                return "Pagamento registrado para o pacote #{$movimentacao->pacote_id} ({$nomePrestador})";
            case 'arquivamento':
                return "Pacote #{$movimentacao->pacote_id} ({$nomePrestador}) arquivado";
            case 'recurso':
                return "Recurso de glosa registrado para o pacote #{$movimentacao->pacote_id} ({$nomePrestador})";
            default:
                return "Pacote #{$movimentacao->pacote_id} ({$nomePrestador}) - {$movimentacao->tipo_movimentacao}";
        }
    }
}