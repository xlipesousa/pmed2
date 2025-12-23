<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapa extends Model
{
    use HasFactory;

    protected $table = 'mapas';
    
    protected $fillable = [
        'numero_mapa',
        'data_criacao',
    ];
    
    protected $casts = [
        'data_criacao' => 'date',
    ];

    // Relacionamento com os pacotes via tabela pivô
    public function pacotes()
    {
        return $this->belongsToMany(Pacote::class, 'mapa_pacote')
            ->using(MapaPacote::class)
            ->withPivot('valor_parcial', 'empenho', 'data_empenho', 'nota_fiscal', 'data_nota_fiscal')
            ->withTimestamps();
    }
    
    // Relacionamento direto com o modelo MapaPacote
    public function mapaPacotes()
    {
        return $this->hasMany(MapaPacote::class);
    }
    
    // Valor total do mapa (soma dos valores parciais)
    public function getValorTotalAttribute()
    {
        return $this->mapaPacotes()->sum('valor_parcial');
    }
}
