<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacoteAnuladoAudit extends Model
{
    use HasFactory;

    /**
     * Nome da tabela
     */
    protected $table = 'pacotes_anulados_audit';

    /**
     * Campos que podem ser preenchidos em massa - REVERSÃO REMOVIDA
     */
    protected $fillable = [
        'pacote_id',
        'valor_fatura_original',
        'valor_pago_original', 
        'valor_pendente_original',
        'valor_glosa_original',
        'valor_pos_lisura_original',
        'valor_recursado_original',
        'valor_deferido_original',
        'numero_fatura',
        'ocs_psa_nome',
        'tipo_pacote_nome',
        'tipo_conta_nome',
        'data_entrada_original',
        'localizacao_no_momento',
        'estado_geral_no_momento',
        'estado_glosa_no_momento',
        'motivo_anulacao',
        'data_anulacao',
        'usuario_anulacao_id',
        'pode_reverter'
        // ❌ REMOVIDO: 'data_reversao', 'usuario_reversao_id', 'motivo_reversao'
    ];

    /**
     * Campos que devem ser tratados como datas
     */
    protected $dates = [
        'data_entrada_original',
        'data_anulacao',
        // ❌ REMOVIDO: 'data_reversao',
        'created_at',
        'updated_at'
    ];

    /**
     * Campos que devem ser castados para tipos específicos
     */
    protected $casts = [
        'valor_fatura_original' => 'decimal:2',
        'valor_pago_original' => 'decimal:2',
        'valor_pendente_original' => 'decimal:2',
        'valor_glosa_original' => 'decimal:2',
        'valor_pos_lisura_original' => 'decimal:2',
        'valor_recursado_original' => 'decimal:2',
        'valor_deferido_original' => 'decimal:2',
        'pode_reverter' => 'boolean',
        'data_anulacao' => 'datetime',
        // ❌ REMOVIDO: 'data_reversao' => 'datetime',
        'data_entrada_original' => 'date'
    ];

    /**
     * Relacionamento com o pacote original
     */
    public function pacote(): BelongsTo
    {
        return $this->belongsTo(Pacote::class, 'pacote_id');
    }

    /**
     * Relacionamento com o usuário que anulou
     */
    public function usuarioAnulacao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_anulacao_id');
    }

    // ❌ REMOVIDO: Relacionamento usuarioReversao()

    /**
     * Scope para anulações por período
     */
    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_anulacao', [$dataInicio, $dataFim]);
    }

    /**
     * Scope para anulações por usuário
     */
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_anulacao_id', $usuarioId);
    }

    /**
     * Calcular valor total original anulado
     */
    public function getValorTotalOriginalAttribute()
    {
        return $this->valor_fatura_original;
    }

    /**
     * Calcular impacto financeiro da anulação
     */
    public function getImpactoFinanceiroAttribute()
    {
        return [
            'fatura' => $this->valor_fatura_original,
            'pago' => $this->valor_pago_original,
            'pendente' => $this->valor_pendente_original,
            'glosa' => $this->valor_glosa_original,
            'total_perdido' => $this->valor_fatura_original - $this->valor_pago_original
        ];
    }
}