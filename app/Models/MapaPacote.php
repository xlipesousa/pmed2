<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MapaPacote extends Pivot
{
    use HasFactory;
    
    protected $table = 'mapa_pacote';
    
    public $incrementing = true;
    
    protected $fillable = [
        'mapa_id',
        'pacote_id',
        'valor_parcial',
        'empenho',
        'data_empenho',
        'nota_fiscal',
        'data_nota_fiscal',
    ];
    
    protected $casts = [
        'data_empenho' => 'date',
        'data_nota_fiscal' => 'date',
        'valor_parcial' => 'decimal:2',
    ];
    
    // Relacionamentos
    public function mapa()
    {
        return $this->belongsTo(Mapa::class);
    }
    
    public function pacote()
    {
        return $this->belongsTo(Pacote::class);
    }
}