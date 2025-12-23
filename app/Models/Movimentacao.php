<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes';
    
    protected $fillable = [
        'pacote_id',
        'user_id',
        'tipo',
        'origem',
        'destino',
        'descricao',
        'estado_geral',
        'estado_glosa'
    ];
    
    public function pacote()
    {
        return $this->belongsTo(Pacote::class);
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}