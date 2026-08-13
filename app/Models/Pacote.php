<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Pacote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pacotes';
    
    protected $fillable = [
        'ocs_psa_id',
        'tipo_id',
        'tipo_conta_id',
        'motivo_glosa_id',
        'descricao_glosa',
        'numero_fatura',
        'data_entrada',
        'valor_fatura',
        'valor_glosa',
        'valor_pos_lisura',
        'valor_pago',
        'valor_pendente',
        'estado_geral',
        'estado_glosa',
        'localizacao_atual',
        'localizacao_anterior',
        'localizacao_fisica',
        'ultima_acao',
        'observacoes',
        'data_notificacao_glosa',
        'data_limite_retirada',
        'data_retirada_oficio',
        'data_recebimento_recurso',
        'valor_recurso_glosa',
        'valor_recursado',
        'valor_deferido',
        // Novos campos de anulação
        'anulado',
        'motivo_anulacao', 
        'data_anulacao',
        'usuario_anulacao_id'
    ];

    protected $casts = [
        'data_entrada' => 'date',
        'valor_fatura' => 'decimal:2',
        'valor_glosa' => 'float',
        'valor_pos_lisura' => 'float',
        'valor_pago' => 'float',
        'valor_pendente' => 'decimal:2',
        'data_notificacao_glosa' => 'datetime',
        'data_limite_retirada' => 'datetime',
        'data_retirada_oficio' => 'datetime',
        'data_recebimento_recurso' => 'datetime',
        // Novos casts para anulação
        'data_anulacao' => 'datetime',
        'anulado' => 'boolean'
    ];

    /**
     * Relacionamento com OCS/PSA
     */
    public function ocsPsa()
    {
        return $this->belongsTo(OcsPsa::class, 'ocs_psa_id');
    }

    /**
     * Relacionamento com Tipo de Pacote
     */
    public function tipoPacote()
    {
        return $this->belongsTo(TipoPacote::class, 'tipo_id');
    }

    /**
     * Relacionamento com Tipo de Conta (pode ser null)
     */
    public function tipoConta()
    {
        return $this->belongsTo(TipoConta::class, 'tipo_conta_id');
    }

    /**
     * Relacionamento com MotivoGlosa (pode ser null)
     */
    public function motivoGlosa()
    {
        return $this->belongsTo(MotivoGlosa::class, 'motivo_glosa_id');
    }

    /**
     * Usuário que anulou o pacote (pode ser null)
     */
    public function usuarioAnulacao()
    {
        return $this->belongsTo(User::class, 'usuario_anulacao_id');
    }

    /**Relacionamento com as movimentações */
    public function movimentacoes()
    {
        return $this->hasMany(MovimentacaoPacote::class, 'pacote_id');
    }
    
    /**Relacionamento com os mapas de pagamento */
    public function mapas()
    {
        return $this->belongsToMany(Mapa::class, 'mapa_pacote')
            ->using(MapaPacote::class)
            ->withPivot('valor_parcial', 'empenho', 'data_empenho', 'nota_fiscal', 'data_nota_fiscal')
            ->withTimestamps();
    }

    /**Relacionamento direto com o modelo MapaPacote */
    public function mapaPacotes()
    {
        return $this->hasMany(MapaPacote::class);
    }

    /**Verifica se o pacote possui uma glosa identificada */
    public function temGlosa()
    {
        return $this->valor_glosa > 0;
    }
    
    /**Verifica se o pacote tem valor pendente */
    public function temValorPendente()
    {
        return $this->valor_pendente > 0;
    }
    
    /**Obtém a porcentagem do valor já pago */
    public function percentualPago()
    {
        if ($this->valor_pos_lisura <= 0) {
            return 0;
        }
        return min(100, round(($this->valor_pago / $this->valor_pos_lisura) * 100));
    }

    /**Verifica se o prazo para retirada do ofício de glosa foi excedido */
    public function prazoRetiradaExcedido()
    {
        if (!$this->data_limite_retirada) {
            return false;
        }
        return now()->gt($this->data_limite_retirada);
    }

    /**Retorna o número de dias restantes para o prazo de retirada do ofício */
    public function diasRetiradaRestantes()
    {
        if (!$this->data_limite_retirada) {
            return null;
        }
        return now()->diffInDays($this->data_limite_retirada, false);
    }

    /**
     * Dias corridos desde a retirada do Ofício de Glosa. Null se o ofício
     * ainda não foi retirado. Ver docs/40-decisoes/ADR-12.md — este prazo
     * é só aviso, nunca dispara ação automática.
     */
    public function diasDesdeRetiradaOficio()
    {
        if (!$this->data_retirada_oficio) {
            return null;
        }
        // Carbon 3 retorna diffInDays() como float (não trunca mais como no
        // Carbon 2) — (int) descarta a fração, senão a badge mostra
        // "45.00152993125 dias" em vez de "45 dias".
        return (int) $this->data_retirada_oficio->diffInDays(now());
    }

    /**
     * Pacotes aguardando recurso de glosa há mais de N dias desde a
     * retirada do Ofício de Glosa (default: config('pmed2.prazo_recurso_dias')).
     * Base do relatório de specs/003-relatorio-prazo-glosa.
     */
    public function scopePrazoRecursoVencido($query, $dias = null)
    {
        $dias = $dias ?? config('pmed2.prazo_recurso_dias');

        return $query->where('estado_glosa', 'Aguardando Recurso de Glosa')
                    ->whereNotNull('data_retirada_oficio')
                    ->where('data_retirada_oficio', '<', now()->subDays($dias));
    }

    /**
     * CORRIGIR: Scope para pacotes válidos
     */
    public function scopeValidos($query)
    {
        return $query->where('anulado', false)
                    ->where('localizacao_atual', '!=', 'anulado')
                    ->where('localizacao_atual', '!=', 'arquivado') // CORREÇÃO: Excluir também arquivados
                    ->where('estado_geral', '!=', 'Anulado');
    }
    
    /**
     * CORRIGIR: Scope para pacotes anulados (QUALQUER critério)
     */
    public function scopeAnulados($query)
    {
        return $query->where(function ($q) {
            $q->where('anulado', true)
              ->orWhere('localizacao_atual', 'anulado')
              ->orWhere('estado_geral', 'Anulado');
        });
    }
    
    /**
     * CORRIGIR: Verificar se pode ser anulado
     */
    public function podeSerAnulado()
    {
        return !$this->anulado 
            && $this->localizacao_atual !== 'anulado'
            && $this->estado_geral !== 'Anulado'
            && $this->localizacao_atual !== 'arquivado'; // CORREÇÃO: Apenas "arquivado"
    }
    
    /**
     * CORRIGIR: Verificar se está anulado
     */
    public function isAnulado()
    {
        return $this->anulado === true 
            || $this->localizacao_atual === 'anulado'
            || $this->estado_geral === 'Anulado';
    }
    
    /**
     * Anula o pacote com motivo e usuário
     */
    public function anular($motivo, $usuarioId)
    {
        if (!$this->podeSerAnulado()) {
            throw new \Exception('Este pacote não pode ser anulado');
        }
        
        $this->update([
            'anulado' => true,
            'data_anulacao' => now(),
            'motivo_anulacao' => $motivo,
            'usuario_anulacao_id' => $usuarioId
        ]);
        
        // Registrar movimentação no histórico
        $this->registrarMovimentacaoAnulacao($motivo, $usuarioId);
        
        return true;
    }
    
    /**
     * Registra a movimentação de anulação no histórico
     */
    private function registrarMovimentacaoAnulacao($motivo, $usuarioId)
    {
        // Usar o helper existente para manter consistência
        if (class_exists('\App\Helpers\AtividadesHelper')) {
            \App\Helpers\AtividadesHelper::registrar(
                $this->id,
                'anulacao',
                'Pacote anulado',
                $motivo,
                $usuarioId
            );
        }
    }
    
    /**
     * Accessor para nome da OCS/PSA com fallback
     */
    public function getOcsPsaNomeAttribute()
    {
        return $this->ocsPsa ? $this->ocsPsa->nome : 'OCS/PSA não encontrada';
    }

    /**
     * Accessor para nome do tipo de pacote com fallback
     */
    public function getTipoPacoteNomeAttribute()
    {
        return $this->tipoPacote ? $this->tipoPacote->nome : 'Tipo não definido';
    }

    /**
     * Accessor para nome do tipo de conta com fallback
     */
    public function getTipoContaNomeAttribute()
    {
        return $this->tipoConta ? $this->tipoConta->nome : 'Tipo de conta não definido';
    }

    /**
     * FASE 2.2: Relacionamento com auditoria de anulação
     */
    public function auditoriaAnulacao()
    {
        return $this->hasOne(PacoteAnuladoAudit::class, 'pacote_id');
    }
}