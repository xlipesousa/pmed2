<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Glosa extends Model
{
    use HasFactory;

    protected $fillable = [
        'pacote_id',
        'motivo_glosa_id',
        'valor',
        'descricao',
        'valor_recursado',
        'valor_deferido'
    ];
    
    protected $casts = [
        'valor' => 'decimal:2',
        'valor_recursado' => 'decimal:2',
        'valor_deferido' => 'decimal:2',
    ];
    
    public function pacote()
    {
        return $this->belongsTo(Pacote::class);
    }
    
    public function motivoGlosa()
    {
        return $this->belongsTo(MotivoGlosa::class, 'motivo_glosa_id');
    }
}