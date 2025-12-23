<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoPacote extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes_pacote';

    protected $fillable = [
        'pacote_id',
        'acao',
        'mensagem',
        'observacao',
        'localizacao_pos_acao',
        'estado_geral',
        'estado_glosa',
        'usuario_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com o pacote
     */
    public function pacote()
    {
        return $this->belongsTo(Pacote::class);
    }

    /**
     * Relacionamento com o usuário
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}